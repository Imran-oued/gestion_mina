<?php
// recu.php
require_once 'securite.php';
require_once 'db.php';

$paiement_id = $_GET['paiement_id'] ?? null;

if (!$paiement_id) {
    die("ID de paiement manquant.");
}

try {
    // Récupérer toutes les infos pour le reçu
    $stmt = $pdo->prepare("
        SELECT 
            p.id AS num_recu,
            p.montant,
            p.methode_paiement,
            p.reference_paiement,
            p.date_paiement,
            f.montant_total,
            f.montant_paye,
            r.description,
            c.nom,
            c.prenom,
            c.telephone,
            c.numero_piece
        FROM paiements p
        JOIN factures f ON p.facture_id = f.id
        JOIN reservations r ON f.reservation_id = r.id
        JOIN clients c ON r.client_id = c.id
        WHERE p.id = :paiement_id
    ");
    $stmt->bindParam(':paiement_id', $paiement_id);
    $stmt->execute();
    $recu = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recu) {
        die("Reçu introuvable.");
    }

    $date_actuelle = date('d/m/Y à H:i');
    $reste = $recu['montant_total'] - $recu['montant_paye'];

} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu #<?= htmlspecialchars($recu['num_recu']) ?> - Mina Voyage</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            color: #2d3748;
        }
        .page {
            max-width: 850px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .receipt-wrapper {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 25px;
            position: relative;
            background-color: #ffffff;
            box-shadow: inset 0 0 0 4px #f8fafc;
        }
        .cut-line {
            border-top: 2px dashed #cbd5e1;
            margin: 40px 0;
            position: relative;
            text-align: left;
        }
        .cut-line::before {
            content: "✂️";
            position: absolute;
            top: -14px;
            left: -15px;
            background: #fff;
            padding: 0 10px;
            font-size: 18px;
        }
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .logo {
            max-width: 140px;
            margin: 0;
            flex-shrink: 0;
        }
        .company-info {
            flex: 1;
            text-align: center;
            padding: 0 15px;
            color: #2d3748;
        }
        .company-name {
            font-size: 20px;
            font-weight: 800;
            color: #2b6cb0;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .company-slogan {
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
            font-style: italic;
        }
        .services-title {
            font-size: 12px;
            font-weight: 700;
            color: #e53e3e;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .services-list {
            font-size: 12px;
            line-height: 1.4;
            color: #4a5568;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .contact-info {
            font-size: 12px;
            line-height: 1.4;
            color: #2d3748;
        }
        .receipt-title {
            text-align: right;
            flex-shrink: 0;
            width: 160px;
        }
        .receipt-title h1 {
            margin: 0;
            font-size: 22px;
            color: #2b6cb0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-box {
            background: #f7fafc;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #edf2f7;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #a0aec0;
            letter-spacing: 0.5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            width: 130px;
            color: #4a5568;
        }
        .info-value {
            color: #1a202c;
            flex: 1;
        }
        
        .description-box {
            background: #fff;
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .bottom-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .payment-method {
            flex: 1;
        }
        .method-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }
        .method-item {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #4a5568;
        }
        .checkbox {
            width: 16px;
            height: 16px;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: transparent;
            transition: all 0.2s;
        }
        .checked {
            background-color: #3182ce;
            border-color: #3182ce;
            color: white;
        }
        
        .amount-table {
            width: 320px;
            border-collapse: collapse;
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .amount-table tr {
            border-bottom: 1px solid #edf2f7;
        }
        .amount-table tr:last-child {
            border-bottom: none;
        }
        .amount-table td {
            padding: 10px 15px;
            font-size: 14px;
        }
        .amount-table td:first-child {
            font-weight: 500;
            color: #4a5568;
        }
        .amount-table td:last-child {
            text-align: right;
            font-weight: 600;
            color: #1a202c;
        }
        .amount-highlight td {
            background-color: #ebf8ff;
            color: #2b6cb0 !important;
            font-size: 16px !important;
            font-weight: 700 !important;
        }
        
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 100px;
            padding-top: 20px;
            border-top: 1px solid #edf2f7;
        }
        .signature-box {
            width: 250px;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px dashed #cbd5e1;
            height: 120px;
            margin-bottom: 10px;
        }
        .signature-label {
            font-size: 13px;
            color: #718096;
            font-weight: 500;
        }
        
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            padding: 12px 24px;
            background: #3182ce;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin: 0 10px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.2);
        }
        .btn:hover { 
            background: #2b6cb0; 
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.3);
        }
        .btn-secondary {
            background: #718096;
            box-shadow: 0 2px 4px rgba(113, 128, 150, 0.2);
        }
        .btn-secondary:hover {
            background: #4a5568;
            box-shadow: 0 4px 6px rgba(113, 128, 150, 0.3);
        }
        
        @media print {
            body { background: #fff; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; font-size: 12px !important; color: #000; }
            @page { margin: 5mm; size: A4 portrait; }
            .page { 
                box-shadow: none; padding: 0; max-width: 100%; border-radius: 0; margin: 0;
                height: 285mm; /* Hauteur d'une page A4 (297mm) moins les marges */
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            .no-print { display: none; }
            
            .receipt-wrapper { 
                border: 1px solid #000 !important; 
                box-shadow: none; 
                padding: 15px !important; 
                page-break-inside: avoid;
                flex: 1; /* Prend la moitié de la page */
                display: flex;
                flex-direction: column;
            }
            .cut-line { margin: 15px 0 !important; border-top: 1px dashed #000 !important; }
            
            .receipt-header { align-items: center !important; padding-bottom: 5px !important; margin-bottom: 5px !important; }
            .logo { max-width: 100px !important; }
            .company-info { padding: 0 10px !important; }
            .company-name { font-size: 16px !important; margin-bottom: 0 !important; }
            .company-slogan { font-size: 10px !important; margin-bottom: 4px !important; }
            .services-title { font-size: 10px !important; margin-bottom: 2px !important; }
            .services-list { font-size: 10px !important; margin-bottom: 4px !important; line-height: 1.2 !important; }
            .contact-info { font-size: 10px !important; line-height: 1.2 !important; }
            
            .receipt-title h1 { font-size: 15px !important; }
            .receipt-title .receipt-num { font-size: 11px !important; margin-top: 2px !important; }
            
            .info-grid { gap: 15px !important; margin-bottom: 15px !important; }
            .info-box { border-color: #999 !important; padding: 10px !important; }
            .info-box h3 { font-size: 11px !important; margin-bottom: 5px !important; }
            .info-row { font-size: 12px !important; margin-bottom: 4px !important; }
            .info-label { width: 120px !important; }
            
            .description-box { 
                border-color: #999 !important; 
                padding: 15px !important; 
                margin-bottom: 15px !important; 
                flex: 1; /* Prend tout l'espace libre au milieu */
                justify-content: center;
            }
            .description-box div:first-child { font-size: 11px !important; margin-bottom: 5px !important; }
            .description-box div:last-child { font-size: 16px !important; font-weight: 600 !important; } /* Agrandit le texte */
            
            .bottom-section { margin-top: 0 !important; }
            .payment-method > div:first-child { font-size: 11px !important; margin-bottom: 5px !important; }
            .method-grid { gap: 8px !important; margin-top: 5px !important; }
            .method-item { font-size: 12px !important; }
            .checkbox { width: 14px !important; height: 14px !important; font-size: 12px !important; margin-right: 5px !important; border-color: #000 !important; }
            
            .amount-table { border-color: #999 !important; width: 300px !important; }
            .amount-table tr { border-color: #999 !important; }
            .amount-table td { padding: 6px 10px !important; font-size: 12px !important; }
            .amount-highlight td { font-size: 14px !important; background-color: #f0f0f0 !important; color: #000 !important; }
            
            .signature-area { margin-top: 15px !important; padding-top: 10px !important; }
            .signature-line { height: 35px !important; margin-bottom: 5px !important; }
            .signature-label { font-size: 11px !important; }
        }
    </style>
</head>
<body>

<?php 
function renderReceipt($title, $recu, $date_impression, $reste) {
    $is_especes = ($recu['methode_paiement'] == 'Espèces') ? 'checked' : '';
    $is_cheque = ($recu['methode_paiement'] == 'Chèque') ? 'checked' : '';
    $is_virement = ($recu['methode_paiement'] == 'Virement') ? 'checked' : '';
    $is_autre = ($recu['methode_paiement'] == 'Carte' || empty($is_especes.$is_cheque.$is_virement)) ? 'checked' : '';
    
    // Format payment date if available, fallback to current time
    $date_paiement = !empty($recu['date_paiement']) ? date('d/m/Y à H:i', strtotime($recu['date_paiement'])) : $date_impression;

    $html = '<div class="receipt-wrapper">';
    
    // En-tête (Header)
    $html .= '<div class="receipt-header">';
    $html .= '<img src="LOGO.jpg" class="logo" alt="Logo Mina Voyage" onerror="this.style.display=\'none\'">';
    $html .= '<div class="company-info">';
    $html .= '<div class="company-name">MINA VOYAGE</div>';
    $html .= '<div class="company-slogan">Votre partenaire de confiance</div>';
    $html .= '<div class="services-title">NOS SERVICES :</div>';
    $html .= '<div class="services-list">';
    $html .= 'Hadj & Oumra &bull; Billetterie &bull; Visa Touristique<br>';
    $html .= 'Location et Vente de Voiture &bull; Adjoint Équipe &bull; Réservation d\'Hôtels Divers';
    $html .= '</div>';
    $html .= '<div class="contact-info">';
    $html .= '<strong>Localisation :</strong> En face du Lycée mixte de Gounghin, Ouagadougou<br>';
    $html .= '<strong>Tél :</strong> +226 50 50 58 50 / 76 46 46 15 &nbsp;|&nbsp; <strong>Whatsapp :</strong> 78 36 30 77';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="receipt-title">';
    $html .= '<h1>' . $title . '</h1>';
    $html .= '<div class="receipt-num">N° ' . str_pad($recu['num_recu'], 6, '0', STR_PAD_LEFT) . '</div>';
    $html .= '</div>';
    $html .= '</div>'; // fin header
    
    // Grille d'infos (Client & Date)
    $html .= '<div class="info-grid">';
    
    // Infos Client
    $html .= '<div class="info-box">';
    $html .= '<h3>Informations Client</h3>';
    $html .= '<div class="info-row"><div class="info-label">Nom complet</div><div class="info-value"><strong>' . htmlspecialchars($recu['nom'] . ' ' . $recu['prenom']) . '</strong></div></div>';
    
    $telephone = !empty($recu['telephone']) ? htmlspecialchars($recu['telephone']) : '-';
    $html .= '<div class="info-row"><div class="info-label">Téléphone</div><div class="info-value">' . $telephone . '</div></div>';
    
    $piece = !empty($recu['numero_piece']) ? htmlspecialchars($recu['numero_piece']) : 'Non renseignée';
    $html .= '<div class="info-row"><div class="info-label">N° Passeport/CNIB</div><div class="info-value">' . $piece . '</div></div>';
    $html .= '</div>'; // fin box client
    
    // Infos Date & Réf
    $html .= '<div class="info-box">';
    $html .= '<h3>Détails de la transaction</h3>';
    $html .= '<div class="info-row"><div class="info-label">Date du paiement</div><div class="info-value">' . $date_paiement . '</div></div>';
    $html .= '<div class="info-row"><div class="info-label">Édité le</div><div class="info-value">' . $date_impression . '</div></div>';
    
    if (!empty($recu['reference_paiement'])) {
        $html .= '<div class="info-row"><div class="info-label">Réf. transaction</div><div class="info-value" style="font-family: monospace; font-size: 15px;">' . htmlspecialchars($recu['reference_paiement']) . '</div></div>';
    }
    $html .= '</div>'; // fin box date
    
    $html .= '</div>'; // fin info-grid
    
    // Description
    $html .= '<div class="description-box">';
    $html .= '<div style="font-size: 12px; color: #a0aec0; text-transform: uppercase; margin-bottom: 5px; font-weight: bold; letter-spacing: 0.5px;">Motif du paiement (Description du dossier)</div>';
    $html .= '<div style="font-size: 16px; color: #1a202c; font-weight: 500;">' . nl2br(htmlspecialchars($recu['description'])) . '</div>';
    $html .= '</div>';
    
    // Section du bas (Méthodes et Montants)
    $html .= '<div class="bottom-section">';
    
    // Méthode de paiement
    $html .= '<div class="payment-method">';
    $html .= '<div style="font-size: 12px; color: #a0aec0; text-transform: uppercase; margin-bottom: 10px; font-weight: bold; letter-spacing: 0.5px;">Mode de règlement</div>';
    $html .= '<div class="method-grid">';
    $html .= '<div class="method-item"><div class="checkbox '.$is_especes.'">✔</div> Espèces</div>';
    $html .= '<div class="method-item"><div class="checkbox '.$is_cheque.'">✔</div> Chèque</div>';
    $html .= '<div class="method-item"><div class="checkbox '.$is_virement.'">✔</div> Virement Bancaire</div>';
    $html .= '<div class="method-item"><div class="checkbox '.$is_autre.'">✔</div> Carte / Autre</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Tableau des montants
    $html .= '<div>';
    $html .= '<table class="amount-table">';
    $html .= '<tr><td>Montant total du dossier</td><td>' . number_format($recu['montant_total'], 0, ',', ' ') . ' FCFA</td></tr>';
    $html .= '<tr class="amount-highlight"><td>Montant encaissé (Ce reçu)</td><td>' . number_format($recu['montant'], 0, ',', ' ') . ' FCFA</td></tr>';
    $html .= '<tr><td>Total déjà réglé</td><td>' . number_format($recu['montant_paye'], 0, ',', ' ') . ' FCFA</td></tr>';
    
    $color_reste = ($reste > 0) ? '#e53e3e' : '#38a169'; // rouge si dette, vert si soldé
    $texte_reste = ($reste > 0) ? 'Reste à payer' : 'Dossier soldé';
    $html .= '<tr><td style="color: '.$color_reste.'; font-weight: bold;">'.$texte_reste.'</td><td style="color: '.$color_reste.'; font-weight: bold;">' . number_format(max(0, $reste), 0, ',', ' ') . ' FCFA</td></tr>';
    
    $html .= '</table>';
    $html .= '</div>';
    
    $html .= '</div>'; // fin bottom-section
    
    // Signatures
    $html .= '<div class="signature-area">';
    $html .= '<div class="signature-box">';
    $html .= '<div class="signature-line"></div>';
    $html .= '<div class="signature-label">Signature du Client</div>';
    $html .= '</div>';
    $html .= '<div class="signature-box">';
    $html .= '<div class="signature-line"></div>';
    $html .= '<div class="signature-label">Cachet et Signature - Mina Voyage</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    $html .= '</div>'; // fin receipt-wrapper
    return $html;
}
?>

<div class="no-print">
    <button class="btn" onclick="window.print()">
        <svg style="width: 20px; height: 20px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        Imprimer / PDF
    </button>
    <a href="dossiers.php" class="btn btn-secondary">
        <svg style="width: 20px; height: 20px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Retour
    </a>
</div>

<div class="page">
    <?= renderReceipt("Reçu de caisse", $recu, $date_actuelle, $reste) ?>
    
    <div class="cut-line"></div>
    
    <?= renderReceipt("Reçu de paiement", $recu, $date_actuelle, $reste) ?>
</div>

</body>
</html>