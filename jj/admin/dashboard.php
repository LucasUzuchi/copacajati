<?php
session_start();

// Protection check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../api/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM cop_inscricoes ORDER BY data_registro DESC");
    $inscricoes = $stmt->fetchAll();
} catch (\PDOException $e) {
    $inscricoes = [];
    $error = "Erro ao buscar dados: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Copa Jiu-Jitsu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #ff1f1f; --bg: #050505; --surface: #121212; --text: #ffffff; --border: rgba(255, 255, 255, 0.1); }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 40px; }
        
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        h1 { font-family: 'Oswald'; text-transform: uppercase; margin: 0; border-left: 5px solid var(--primary); padding-left: 15px; }
        
        .btn-logout { background: transparent; border: 1px solid var(--primary); color: var(--primary); padding: 8px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .btn-logout:hover { background: var(--primary); color: white; }

        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--surface); padding: 20px; border-radius: 12px; border: 1px solid var(--border); }
        .stat-card span { display: block; font-size: 0.8rem; color: #a0a0a0; text-transform: uppercase; }
        .stat-card strong { font-size: 2rem; font-family: 'Oswald'; color: var(--primary); }
        
        table { width: 100%; border-collapse: collapse; background: var(--surface); border-radius: 12px; overflow: hidden; border: 1px solid var(--border); }
        th { background: #1a1a1a; color: white; text-align: left; padding: 15px; font-family: 'Oswald'; text-transform: uppercase; font-size: 0.9rem; border-bottom: 2px solid var(--border); }
        td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 0.9rem; color: #e4e4e7; }
        tr:hover { background: #1a1a1a; }
        
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        .badge-pendente { background: rgba(254, 243, 199, 0.1); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }
        .badge-pago { background: rgba(220, 252, 231, 0.1); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.3); }
        
        .faixa { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 5px; border: 1px solid #555; }
        .faixa-branca { background: white; }
        .faixa-azul { background: blue; }
        .faixa-roxa { background: purple; }
        .faixa-marrom { background: brown; }
        .faixa-preta { background: black; }

        .actions { display: flex; gap: 10px; }
        .action-btn { background: #27272a; color: white; border: none; width: 32px; height: 32px; border-radius: 6px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
        .action-btn:hover { background: #3f3f46; transform: translateY(-2px); }
        .action-btn.btn-pay { color: #4ade80; }
        .action-btn.btn-edit { color: #60a5fa; }
        .action-btn.btn-delete { color: #f87171; }

        /* Modal Styles */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: var(--surface); padding: 30px; border-radius: 20px; width: 100%; max-width: 500px; border: 1px solid var(--border); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h2 { font-family: 'Oswald'; text-transform: uppercase; margin: 0; }
        .close-modal { cursor: pointer; font-size: 1.5rem; color: #a0a0a0; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.8rem; color: #a0a0a0; margin-bottom: 5px; text-transform: uppercase; }
        .form-group input, .form-group select { width: 100%; padding: 10px; background: #1a1a1a; border: 1px solid var(--border); border-radius: 8px; color: white; }
        
        .btn-save { background: var(--primary); color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-family: 'Oswald'; font-weight: 700; text-transform: uppercase; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body>

    <header>
        <h1>Controle de Atletas</h1>
        <a href="login.php?logout=1" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
    </header>

    <div class="stats">
        <div class="stat-card">
            <span>Total de Inscritos</span>
            <strong><?php echo count($inscricoes); ?></strong>
        </div>
        <div class="stat-card">
            <span>Arrecadação Prevista</span>
            <strong>R$ <?php 
                $total = array_sum(array_column($inscricoes, 'valor'));
                echo number_format($total, 2, ',', '.');
            ?></strong>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div style="background: rgba(239, 68, 68, 0.1); color: #f87171; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2);">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Equipe</th>
                <th>Faixa</th>
                <th>Plano</th>
                <th>Status</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inscricoes as $atleta): ?>
            <tr id="row-<?php echo $atleta['id']; ?>">
                <td style="font-weight: 600;">
                    <?php echo htmlspecialchars($atleta['nome']); ?><br>
                    <small style="font-weight: normal; color: #71717a;"><?php echo htmlspecialchars($atleta['cpf']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($atleta['equipe']); ?></td>
                <td>
                    <span class="faixa faixa-<?php echo strtolower($atleta['faixa']); ?>"></span>
                    <?php echo htmlspecialchars($atleta['faixa']); ?>
                </td>
                <td style="font-size: 0.8rem;">
                    <?php echo htmlspecialchars($atleta['plano']); ?><br>
                    <strong>R$ <?php echo number_format($atleta['valor'], 2, ',', '.'); ?></strong>
                </td>
                <td>
                    <span class="badge badge-<?php echo $atleta['status_pagamento']; ?>" id="status-<?php echo $atleta['id']; ?>">
                        <?php echo $atleta['status_pagamento']; ?>
                    </span>
                </td>
                <td style="font-size: 0.8rem; color: #71717a;">
                    <?php echo date('d/m/y H:i', strtotime($atleta['data_registro'])); ?>
                </td>
                <td>
                    <div class="actions">
                        <button class="action-btn btn-pay" onclick="confirmPayment(<?php echo $atleta['id']; ?>)" title="Confirmar Pagamento">
                            <i class="fa-solid fa-check"></i>
                        </button>
                        <button class="action-btn btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($atleta)); ?>)" title="Editar Atleta">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="action-btn btn-delete" onclick="deleteAthlete(<?php echo $atleta['id']; ?>)" title="Excluir Inscrição">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Atleta</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <form id="editForm">
                <input type="hidden" name="id" id="edit-id">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" id="edit-nome" required>
                </div>
                <div class="form-group">
                    <label>Equipe</label>
                    <input type="text" name="equipe" id="edit-equipe" required>
                </div>
                <div class="form-group">
                    <label>Faixa</label>
                    <select name="faixa" id="edit-faixa">
                        <option value="Branca">Branca</option>
                        <option value="Azul">Azul</option>
                        <option value="Roxa">Roxa</option>
                        <option value="Marrom">Marrom</option>
                        <option value="Preta">Preta</option>
                    </select>
                </div>
                <button type="submit" class="btn-save">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function openEditModal(atleta) {
            document.getElementById('edit-id').value = atleta.id;
            document.getElementById('edit-nome').value = atleta.nome;
            document.getElementById('edit-equipe').value = atleta.equipe;
            document.getElementById('edit-faixa').value = atleta.faixa;
            document.getElementById('editModal').classList.add('active');
        }

        async function confirmPayment(id) {
            if (!confirm('Deseja confirmar o pagamento deste atleta?')) return;
            
            try {
                const response = await fetch('../api/admin_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'pay', id: id })
                });
                const result = await response.json();
                if (result.success) {
                    const badge = document.getElementById('status-' + id);
                    badge.className = 'badge badge-pago';
                    badge.innerText = 'pago';
                } else {
                    alert('Erro: ' + result.message);
                }
            } catch (e) {
                alert('Erro ao processar requisição');
            }
        }

        async function deleteAthlete(id) {
            if (!confirm('TEM CERTEZA? Esta ação não pode ser desfeita.')) return;
            
            try {
                const response = await fetch('../api/admin_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', id: id })
                });
                const result = await response.json();
                if (result.success) {
                    document.getElementById('row-' + id).remove();
                } else {
                    alert('Erro: ' + result.message);
                }
            } catch (e) {
                alert('Erro ao processar requisição');
            }
        }

        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = {
                action: 'edit',
                id: formData.get('id'),
                nome: formData.get('nome'),
                equipe: formData.get('equipe'),
                faixa: formData.get('faixa')
            };

            try {
                const response = await fetch('../api/admin_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.success) {
                    location.reload(); // Simplest way to update UI
                } else {
                    alert('Erro: ' + result.message);
                }
            } catch (e) {
                alert('Erro ao processar requisição');
            }
        });
    </script>

</body>
</html>

