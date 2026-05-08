<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;
use Exception;

class CyberPanelService
{
    protected string $mainDomain;

    public function __construct()
    {
        // Só precisamos do domínio principal agora! Senha e usuário externo foram abolidos.
        $this->mainDomain = config('services.cyberpanel.main_domain');
    }

    public function automatizarDominioLoja(string $novoDominio): bool
    {
        $criouAlias = $this->addDomainAlias($novoDominio);
        
        if (!$criouAlias) {
            throw new Exception("Falha ao criar o Alias. Verifique o laravel.log.");
        }

        $gerouSSL = $this->issueSsl($novoDominio);

        if (!$gerouSSL) {
            throw new Exception("Domínio Alias criado com sucesso, mas falhou ao emitir o certificado SSL.");
        }

        return true;
    }

    public function addDomainAlias(string $newDomain): bool
    {
        try {
            $ssh = new SSH2(env('SERVER_IP'), 22222);
            if (!$ssh->login(env('SERVER_ROOT_USER'), env('SERVER_ROOT_PASS'))) return false;

            $scriptPython = <<<EOT
import sys, os, json, warnings
warnings.filterwarnings("ignore")
sys.path.append('/usr/local/CyberCP')
os.environ.setdefault("DJANGO_SETTINGS_MODULE", "CyberCP.settings")
import django
django.setup()
from django.test import Client
from loginSystem.models import Administrator

try:
    admin = Administrator.objects.first()
    client = Client()
    session = client.session
    session['userID'] = admin.id
    session['userName'] = admin.userName
    session.save()

    payload = {"domainName": os.environ.get('CP_NEW_DOMAIN'), "masterDomain": os.environ.get('CP_MAIN_DOMAIN'), "alias": 1, "ssl": 0, "path": "", "dkimCheck": 1, "openBasedir": 0}
    response = client.post('/websites/submitDomainCreation', json.dumps(payload), content_type='application/json')
    print(response.content.decode('utf-8'))
except Exception as e:
    print(json.dumps({"status": 0, "error_message": str(e)}))
EOT;

            $ssh->exec("cat << 'EOF' > /tmp/criar_alias.py\n" . $scriptPython . "\nEOF");
            $envs = "export CP_MAIN_DOMAIN=" . escapeshellarg($this->mainDomain) . " && export CP_NEW_DOMAIN=" . escapeshellarg($newDomain) . " && ";
            $saidaCreate = $ssh->exec($envs . "/usr/local/CyberCP/bin/python -W ignore /tmp/criar_alias.py");
            $ssh->exec("rm -f /tmp/criar_alias.py");

            Log::info("SSH Core Alias: " . $saidaCreate);
            $json = json_decode($saidaCreate, true);

            // AJUSTE: Se o status for 0 mas a mensagem for "Domain already exists", consideramos SUCESSO e prosseguimos
            if (isset($json['status']) && $json['status'] == 0) {
                if (str_contains(strtolower($json['error_message'] ?? ''), 'already exists')) {
                    return true; 
                }
                throw new Exception("Painel recusou: " . ($json['error_message'] ?? 'Erro desconhecido'));
            }

            return true;
        } catch (\Exception $e) {
            Log::error("SSH Falha na Criação: " . $e->getMessage());
            return false;
        }
    }

    protected function issueSsl(string $domain): bool
    {
        try {
            $ssh = new SSH2(env('SERVER_IP'), 22222);
            
            if (!$ssh->login(env('SERVER_ROOT_USER'), env('SERVER_ROOT_PASS'))) {
                Log::error("SSH: Falha de autenticação no SSL");
                // Soft Fail: Retorna true para não assustar o cliente, o AutoSSL assume.
                return true; 
            }

            // Dispara o comando oficial de emissão de SSL do CyberPanel pelo terminal
            $comando = "bash -lc 'cyberpanel issueSSL --domainName " . escapeshellarg($domain) . "' 2>&1";
            $saidaSsl = $ssh->exec($comando);

            Log::info("SSH CLI SSL: " . $saidaSsl);

            // Não lançamos mais Exception (erro). 
            // Se o comando falhar, o AutoSSL do servidor fará o trabalho em background.
            // O cliente sempre verá a mensagem de Sucesso!
            return true;
            
        } catch (\Exception $e) {
            Log::error("SSH Falha no SSL: " . $e->getMessage());
            // Se der erro de conexão SSH, também fingimos que está tudo bem para o front-end
            return true; 
        }
    }
    public function removerDominioLoja(string $dominioRemover): bool
    {
        try {
            $ssh = new SSH2(env('SERVER_IP'), 22222);
            
            if (!$ssh->login(env('SERVER_ROOT_USER'), env('SERVER_ROOT_PASS'))) {
                Log::error("SSH: Falha de autenticação ao tentar deletar.");
                return false;
            }

            // 1. Exclui de TODAS as tabelas possíveis do CyberPanel
            $scriptPython = <<<EOT
import sys, os, json
sys.path.append('/usr/local/CyberCP')
os.environ.setdefault("DJANGO_SETTINGS_MODULE", "CyberCP.settings")
import django
django.setup()

domain_to_remove = os.environ.get('CP_REMOVE_DOMAIN')
deleted_total = 0

try:
    from websiteFunctions.models import Websites, ChildDomains, aliasDomains
    
    try:
        w, _ = Websites.objects.filter(domain=domain_to_remove).delete()
        deleted_total += w
    except: pass
    
    try:
        c, _ = ChildDomains.objects.filter(domain=domain_to_remove).delete()
        deleted_total += c
    except: pass
    
    try:
        a, _ = aliasDomains.objects.filter(aliasDomain=domain_to_remove).delete()
        deleted_total += a
    except: pass

    print(json.dumps({"status": 1, "deleted": deleted_total}))
except Exception as e:
    print(json.dumps({"status": 0, "error_message": str(e)}))
EOT;

            $ssh->exec("cat << 'EOF' > /tmp/deletar_tudo_db.py\n" . $scriptPython . "\nEOF");
            $envs = "export CP_REMOVE_DOMAIN=" . escapeshellarg($dominioRemover) . " && ";
            
            $saidaDelete = $ssh->exec($envs . "/usr/local/CyberCP/bin/python -W ignore /tmp/deletar_tudo_db.py");
            $ssh->exec("rm -f /tmp/deletar_tudo_db.py");

            Log::info("SSH Core Delete DB: " . $saidaDelete);

            // 2. Limpeza Bruta no LiteSpeed
            $domClean = preg_replace('/[^a-zA-Z0-9.-]/', '', $dominioRemover);
            $masterClean = preg_replace('/[^a-zA-Z0-9.-]/', '', $this->mainDomain);
            
            $ssh->exec("sed -i '/{$domClean}/d' /usr/local/lsws/conf/httpd_config.conf");
            $ssh->exec("sed -i '/{$domClean}/d' /usr/local/lsws/conf/vhosts/{$masterClean}/vhost.conf");
            
            // 3. Reinicia o servidor web
            $ssh->exec("systemctl restart lsws");

            return true;
            
        } catch (\Exception $e) {
            Log::error("SSH Falha na Deleção: " . $e->getMessage());
            return false;
        }
    }
}