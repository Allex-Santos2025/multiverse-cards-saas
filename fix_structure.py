with open("app/Livewire/GlobalSearch.php", "r", encoding="utf-8") as f:
    lines = f.readlines()

# Encontrar o índice da linha que contém $termNormalized
target_idx = None
for i, line in enumerate(lines):
    if "$termNormalized = mb_strtolower" in line:
        target_idx = i
        break

if target_idx is not None:
    # Mostra 15 linhas antes para diagnóstico visual se necessário
    print("Linhas anteriores ao $termNormalized:")
    for l in lines[max(0, target_idx - 15):target_idx + 2]:
        print(l, end="")

    # Remover linhas que contenham apenas fechamentos órfãos logo acima
    start_cleanup = target_idx - 1
    while start_cleanup >= 0 and lines[start_cleanup].strip() in ["}", ""];
        start_cleanup -= 1

    # Após o loop dos hits, precisamos exatamente fechar:
    # 1. foreach ($printsAgrupados as $sidFantasma => $variants)
    # 2. if ($this->isLojista)
    # 3. foreach ($hits as $hit)
    # O foreach ($variants as ...) já fechou logo após o if (!$inEstoque).
    
    # Vamos reescrever com as 4 chaves exatas de identação estrutural:
    clean_closing = [
        "                    }\n",
        "                }\n",
        "            }\n",
        "        }\n",
        "\n"
    ]
    
    # Encontra o último comando útil do bloco de fantasmas (o fechamento do if !$inEstoque)
    cutoff = None
    for j in range(target_idx - 1, -1, -1):
        if "$globalResults[] = $this->generateGhostData" in lines[j]:
            # A próxima linha com '}' fecha o if (!$inEstoque)
            for k in range(j + 1, target_idx):
                if lines[k].strip() == "}":
                    cutoff = k + 1
                    break
            break

    if cutoff is not None:
        new_lines = lines[:cutoff] + clean_closing + lines[target_idx:]
        with open("app/Livewire/GlobalSearch.php", "w", encoding="utf-8") as f:
            f.writelines(new_lines)
        print("\nEstrutura corrigida com sucesso!")
    else:
        print("\nNão foi possível achar o corte exato.")
