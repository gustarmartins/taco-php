<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Rápida e Calculadora - Tabela TACO</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 900px; margin: auto; }
        .search-form { margin-bottom: 20px; display: flex; gap: 10px; }
        .search-form input[type="text"] { padding: 8px; flex-grow: 1; }
        .search-form input[type="submit"] { padding: 8px 15px; }
        .results table, .diet-list table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .results th, .results td, .diet-list th, .diet-list td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 0.9em; }
        .results th, .diet-list th { background-color: #f2f2f2; }
        .no-results { color: #777; margin-top:10px; }
        .add-btn { padding: 5px 10px; cursor: pointer; }
        .diet-summary { margin-top: 20px; padding: 15px; background-color: #e9f5e9; border: 1px solid #c8e6c9; }
        .diet-summary h3 { margin-top: 0; }
        .flex-container { display: flex; gap: 20px; }
        .search-container { flex: 2; }
        .diet-container { flex: 1; border-left: 1px solid #eee; padding-left: 20px;}
        @media (max-width: 768px) { .flex-container { flex-direction: column; } .diet-container { border-left: none; padding-left: 0; border-top: 1px solid #eee; padding-top: 20px; } }
    </style>
</head>
<body>
<div class="container">
    <h1>TACO NutriCalc</h1>

    <div class="flex-container">
        <div class="search-container">
            <h2>Consulta Rápida de Alimentos</h2>
            <form class="search-form" method="GET" action="index.php">
                <input type="text" name="termo_busca" placeholder="Digite o nome do alimento..." value="<?php echo isset($_GET['termo_busca']) ? htmlspecialchars($_GET['termo_busca']) : ''; ?>">
                <input type="submit" value="Buscar">
            </form>

            <div class="results">
                <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "taco_db";
                $resultados_busca = [];
                $termo_busca_atual = '';

                if (isset($_GET['termo_busca'])) {
                    $termo_busca_atual = trim($_GET['termo_busca']);
                    if (!empty($termo_busca_atual) && strlen($termo_busca_atual) >= 2) {
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        if ($conn->connect_error) {
                            die("<p class='no-results'>Erro de conexão: " . $conn->connect_error . "</p>");
                        }
                        $conn->set_charset("utf8mb4");

                        $sql = "SELECT codigoOriginal, nome, categoria, umidade, energiaKcal, proteina, lipidios_total, carboidratos 
                                    FROM alimento 
                                    WHERE LOWER(nome) LIKE CONCAT(LOWER(?), '%') 
                                    ORDER BY nome ASC LIMIT 50"; // Limite para não sobrecarregar

                        $stmt = $conn->prepare($sql);
                        if ($stmt) {
                            $param_termo = $termo_busca_atual;
                            $stmt->bind_param("s", $param_termo);
                            $stmt->execute();
                            $result_query = $stmt->get_result();
                            if ($result_query->num_rows > 0) {
                                while($row = $result_query->fetch_assoc()) {
                                    $resultados_busca[] = $row;
                                }
                            }
                            $stmt->close();
                        } else {
                            echo "<p class='no-results'>Erro ao preparar consulta.</p>";
                        }
                        $conn->close();
                    } elseif (strlen($termo_busca_atual) < 2 && strlen($termo_busca_atual) > 0) {
                        echo "<p class='no-results'>Digite ao menos 2 caracteres.</p>";
                    }
                }

                if (!empty($termo_busca_atual) && strlen($termo_busca_atual) >= 2) {
                    if (!empty($resultados_busca)) {
                        echo "<h3>Resultados para: \"" . htmlspecialchars($termo_busca_atual) . "\"</h3>";
                        echo "<table>";
                        echo "<tr><th>Alimento</th><th>Categoria</th><th>Kcal</th><th>Prot.</th><th>Carb.</th><th>Lip.</th><th>Ação</th></tr>";
                        foreach ($resultados_busca as $alimento) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($alimento['nome']) . "</td>";
                            echo "<td>" . htmlspecialchars($alimento['categoria']) . "</td>";
                            echo "<td>" . (isset($alimento['energiaKcal']) ? htmlspecialchars($alimento['energiaKcal']) : 'N/A') . "</td>";
                            echo "<td>" . (isset($alimento['proteina']) ? htmlspecialchars($alimento['proteina']) : 'N/A') . "</td>";
                            echo "<td>" . (isset($alimento['carboidratos']) ? htmlspecialchars($alimento['carboidratos']) : 'N/A') . "</td>";
                            echo "<td>" . (isset($alimento['lipidios_total']) ? htmlspecialchars($alimento['lipidios_total']) : 'N/A') . "</td>";
                            // Botão Adicionar com atributos data-* para o JavaScript pegar
                            echo "<td><button class='add-btn' 
                                            data-nome='" . htmlspecialchars($alimento['nome'], ENT_QUOTES) . "'
                                            data-kcal='" . ($alimento['energiaKcal'] ?? 0) . "'
                                            data-proteina='" . ($alimento['proteina'] ?? 0) . "'
                                            data-carboidrato='" . ($alimento['carboidratos'] ?? 0) . "'
                                            data-lipidios='" . ($alimento['lipidios_total'] ?? 0) . "'
                                        >+</button></td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "<p class='no-results'>Nenhum alimento encontrado para \"" . htmlspecialchars($termo_busca_atual) . "\".</p>";
                    }
                } elseif (isset($_GET['termo_busca']) && empty($termo_busca_atual) && $_SERVER['REQUEST_METHOD'] === 'GET' && count($_GET) > 0) {
                    echo "<p class='no-results'>Por favor, digite um termo para buscar.</p>";
                }
                ?>
            </div>
        </div>

        <div class="diet-container">
            <h2>Dieta Rápida</h2>
            <div id="lista-dieta-rapida">
                <p class="no-results">Nenhum alimento adicionado ainda.</p>
            </div>
            <div class="diet-summary" id="resumo-dieta-rapida">
                <h3>Totais da Dieta Rápida:</h3>
                <p>Calorias: <span id="total-kcal">0</span> kcal</p>
                <p>Proteínas: <span id="total-proteina">0</span> g</p>
                <p>Carboidratos: <span id="total-carboidrato">0</span> g</p>
                <p>Lipídios: <span id="total-lipidios">0</span> g</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Array para armazenar os alimentos da dieta rápida
    let dietaAtual = [];

    // Seleciona todos os botões de adicionar
    const addButtons = document.querySelectorAll('.add-btn');

    // Seleciona os elementos onde vamos mostrar a lista e os totais
    const listaDietaDiv = document.getElementById('lista-dieta-rapida');
    const totalKcalSpan = document.getElementById('total-kcal');
    const totalProteinaSpan = document.getElementById('total-proteina');
    const totalCarboidratoSpan = document.getElementById('total-carboidrato');
    const totalLipidiosSpan = document.getElementById('total-lipidios');

    addButtons.forEach(button => {
        button.addEventListener('click', function() {
            const nome = this.dataset.nome;
            const kcalPer100g = parseFloat(this.dataset.kcal);
            const proteinaPer100g = parseFloat(this.dataset.proteina);
            const carboidratoPer100g = parseFloat(this.dataset.carboidrato);
            const lipidiosPer100g = parseFloat(this.dataset.lipidios);

            const quantidadeGrams = prompt(`Digite a quantidade em gramas para "${nome}":`, "100");

            if (quantidadeGrams !== null && !isNaN(parseFloat(quantidadeGrams)) && parseFloat(quantidadeGrams) > 0) {
                const quantidade = parseFloat(quantidadeGrams);
                dietaAtual.push({
                    nome: nome,
                    quantidade: quantidade,
                    kcalBase: kcalPer100g,
                    proteinaBase: proteinaPer100g,
                    carboidratoBase: carboidratoPer100g,
                    lipidiosBase: lipidiosPer100g
                });
                atualizarListaDietaETotais();
            } else if (quantidadeGrams !== null) {
                alert("Por favor, insira uma quantidade válida em gramas.");
            }
        });
    });

    function atualizarListaDietaETotais() {
        // Limpa a lista atual na tela
        listaDietaDiv.innerHTML = '';

        if (dietaAtual.length === 0) {
            listaDietaDiv.innerHTML = '<p class="no-results">Nenhum alimento adicionado ainda.</p>';
            totalKcalSpan.textContent = '0';
            totalProteinaSpan.textContent = '0';
            totalCarboidratoSpan.textContent = '0';
            totalLipidiosSpan.textContent = '0';
            return;
        }

        const table = document.createElement('table');
        let html = '<tr><th>Alimento</th><th>Porção (g)</th><th>Kcal</th><th>P</th><th>C</th><th>L</th><th>Ação</th></tr>';

        let totalKcal = 0;
        let totalProteina = 0;
        let totalCarboidrato = 0;
        let totalLipidios = 0;

        dietaAtual.forEach((item, index) => {
            const fator = item.quantidade / 100.0;
            const kcalItem = item.kcalBase * fator;
            const proteinaItem = item.proteinaBase * fator;
            const carboidratoItem = item.carboidratoBase * fator;
            const lipidiosItem = item.lipidiosBase * fator;

            totalKcal += kcalItem;
            totalProteina += proteinaItem;
            totalCarboidrato += carboidratoItem;
            totalLipidios += lipidiosItem;

            html += `<tr>
                            <td>${item.nome}</td>
                            <td>${item.quantidade.toFixed(0)}</td>
                            <td>${kcalItem.toFixed(1)}</td>
                            <td>${proteinaItem.toFixed(1)}</td>
                            <td>${carboidratoItem.toFixed(1)}</td>
                            <td>${lipidiosItem.toFixed(1)}</td>
                            <td><button onclick="removerItemDieta(${index})">X</button></td>
                         </tr>`;
        });
        table.innerHTML = html;
        listaDietaDiv.appendChild(table);

        // Atualiza os totais
        totalKcalSpan.textContent = totalKcal.toFixed(1);
        totalProteinaSpan.textContent = totalProteina.toFixed(1);
        totalCarboidratoSpan.textContent = totalCarboidrato.toFixed(1);
        totalLipidiosSpan.textContent = totalLipidios.toFixed(1);
    }

    function removerItemDieta(index) {
        if (index >= 0 && index < dietaAtual.length) {
            dietaAtual.splice(index, 1); // Remove o item do array
            atualizarListaDietaETotais(); // Re-renderiza a lista e os totais
        }
    }

    // Chama uma vez no carregamento para o caso de haver estado salvo (não implementado aqui)
    // ou para exibir a mensagem "Nenhum alimento adicionado"
    atualizarListaDietaETotais();
</script>
</body>
</html>