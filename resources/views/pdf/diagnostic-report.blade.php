<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Diagnostic piscine — Dlo Azur Piscines</title>
    <style>
        /* DomPDF — CSS 2.1 uniquement. Pas de Flexbox/Grid/Tailwind. */
        /* Couleurs approchées depuis les tokens OKLCH brand (seule exception hex — DomPDF ne parse pas oklch). */
        /* navy  oklch(0.232 0.052 251) ≈ #1a3a5c    azure oklch(0.53 0.2 253)  ≈ #0080ff */
        /* sand  oklch(0.97  0.005 60) ≈  #f7f4ef    ink   oklch(0.18 0.015 250) ≈ #1e2128 */
        /* ambre oklch(0.78  0.13  80) ≈  #d97706 (bg tinted) */

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #1e2128;
            line-height: 1.5;
            background: #f7f4ef;
        }

        .wrapper {
            max-width: 700px;
            margin: 0 auto;
            background: #ffffff;
        }

        /* ── En-tête marine ──────────────────────────────────────────── */
        .header {
            background: #154c79;
            color: #f7f4ef;
            padding: 24px 32px 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-logo-cell {
            width: 56px;
            vertical-align: middle;
        }
        .header-logo {
            width: 48px;
            height: 48px;
        }
        .header-title-cell {
            vertical-align: middle;
            padding-left: 16px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 700;
            color: #f7f4ef;
            margin: 0;
        }
        .header .subtitle {
            font-size: 12px;
            color: #c0d6e8;
            margin-top: 2px;
        }
        .header-date-cell {
            text-align: right;
            vertical-align: middle;
            font-size: 11px;
            color: #c0d6e8;
        }

        /* ── Ligne de séparation azure ─────────────────────────────── */
        .azure-rule {
            height: 3px;
            background: #0080ff;
            border: none;
            margin: 0;
        }

        /* ── Corps ──────────────────────────────────────────────────── */
        .body {
            padding: 28px 32px;
            background: #ffffff;
        }

        .section {
            margin-bottom: 24px;
        }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #0080ff;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e0d8;
            padding-bottom: 5px;
        }

        /* ── Tableau infos piscine + mesures ─────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .data-table td {
            padding: 6px 8px;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) td {
            background: #f7f4ef;
        }
        .label-cell {
            width: 42%;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #6b7280;
        }
        .value-cell {
            color: #1e2128;
        }

        /* ── Diagnostic + confiance ─────────────────────────────── */
        .diag-box {
            background: #f7f4ef;
            border-left: 3px solid #0080ff;
            padding: 12px 16px;
            margin-bottom: 8px;
        }
        .diag-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .diag-value {
            font-size: 14px;
            font-weight: 700;
            color: #154c79;
        }
        .confidence-box {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .confidence-high   { background: #d1fae5; color: #065f46; }
        .confidence-medium { background: #fef3c7; color: #92400e; }
        .confidence-low    { background: #fee2e2; color: #991b1b; }

        /* ── Plan d'action ──────────────────────────────────────── */
        .action-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .action-table th {
            background: #154c79;
            color: #f7f4ef;
            padding: 7px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }
        .action-table td {
            padding: 8px 10px;
            vertical-align: top;
            border-bottom: 1px solid #e5e0d8;
        }
        .action-table tr:nth-child(even) td {
            background: #f7f4ef;
        }
        .step-num {
            width: 28px;
            font-weight: 700;
            color: #0080ff;
            text-align: center;
        }
        .param-name {
            width: 22%;
            font-weight: 700;
            color: #1e2128;
        }
        .measure-cell {
            width: 14%;
            text-align: right;
            color: #6b7280;
            font-size: 11px;
        }
        .dose-cell {
            width: 22%;
            font-weight: 700;
            color: #154c79;
        }
        .note-cell {
            color: #6b7280;
            font-size: 11px;
        }

        /* ── Bloc sécurité ambre ────────────────────────────────── */
        .safety-block {
            background: #fffbeb;
            border: 1px solid #d97706;
            border-left: 4px solid #d97706;
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        .safety-title {
            font-size: 12px;
            font-weight: 700;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 6px;
        }
        .safety-text {
            font-size: 12px;
            color: #78350f;
            line-height: 1.6;
        }

        /* ── Avertissement / disclaimer ─────────────────────────── */
        .disclaimer-box {
            background: #f7f4ef;
            border: 1px solid #c8c2b8;
            padding: 14px 16px;
            margin-bottom: 20px;
        }
        .disclaimer-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .disclaimer-text {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.6;
        }

        /* ── Pied de page ───────────────────────────────────────── */
        .footer {
            background: #154c79;
            color: #c0d6e8;
            padding: 14px 32px;
            text-align: center;
            font-size: 11px;
        }
        .footer strong {
            color: #f7f4ef;
        }
        .footer .professional-notice {
            font-size: 10px;
            color: #8eb8d0;
            margin-top: 4px;
        }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- En-tête marine avec logo et titre -->
    <div class="header">
        <table class="header-table">
            <tr>
                @php
                    $logoPath = public_path('assets/brand/logo-mark.svg');
                    $hasLogo  = file_exists($logoPath);
                @endphp
                @if ($hasLogo)
                <td class="header-logo-cell">
                    {{-- logo via public_path — pas d'URL externe (DomPDF Pitfall 3) --}}
                    <img src="{{ $logoPath }}" alt="Dlo Azur" class="header-logo">
                </td>
                @endif
                <td class="header-title-cell">
                    <h1>Votre diagnostic piscine — Dlo Azur</h1>
                    <div class="subtitle">Dlo Azur Piscines · Martinique</div>
                </td>
                <td class="header-date-cell">
                    Généré le {{ $diagnostic->created_at ? $diagnostic->created_at->format('d/m/Y') : now()->format('d/m/Y') }}<br>
                    Réf. {{ $diagnostic->id }}
                </td>
            </tr>
        </table>
    </div>
    <hr class="azure-rule">

    <div class="body">

        <!-- ① Infos piscine + mesures -->
        <div class="section">
            <div class="section-title">Infos piscine &amp; mesures</div>
            <table class="data-table">
                @if ($diagnostic->volume_m3)
                <tr>
                    <td class="label-cell">Volume</td>
                    <td class="value-cell">{{ number_format((float) $diagnostic->volume_m3, 1, ',', ' ') }} m³</td>
                </tr>
                @endif
                @if ($diagnostic->type_probleme)
                <tr>
                    <td class="label-cell">Problème déclaré</td>
                    <td class="value-cell">{{ $diagnostic->type_probleme }}</td>
                </tr>
                @endif
                @if (!empty($diagnostic->mesures) && is_array($diagnostic->mesures))
                    @php $mesures = $diagnostic->mesures; @endphp
                    @foreach (['ph' => 'pH', 'chlore' => 'Chlore libre (mg/L)', 'alcalinite' => 'Alcalinité / TAC (mg/L)', 'stabilisant' => 'Stabilisant (mg/L)', 'selPpm' => 'Sel (g/L)', 'chloreTotal' => 'Chlore total (mg/L)', 'th' => 'TH (mg/L)'] as $key => $label)
                        @if (isset($mesures[$key]) && $mesures[$key] !== null && $mesures[$key] !== '')
                        <tr>
                            <td class="label-cell">{{ $label }}</td>
                            <td class="value-cell">{{ $mesures[$key] }}</td>
                        </tr>
                        @endif
                    @endforeach
                @endif
                @if ($diagnostic->disclaimer_accepted_at)
                <tr>
                    <td class="label-cell">Conditions acceptées le</td>
                    <td class="value-cell">{{ $diagnostic->disclaimer_accepted_at->format('d/m/Y à H:i') }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- ② Diagnostic + confiance -->
        @php
            $recommandations = $diagnostic->recommandations ?? [];
            $confidence = null;
            if (is_array($recommandations) && !empty($recommandations)) {
                // confidence peut être stockée en dernière entrée spéciale ou dans la première carte
                $firstRec = reset($recommandations);
                if (is_array($firstRec) && isset($firstRec['confidence'])) {
                    $confidence = $firstRec['confidence'];
                }
            }
        @endphp
        <div class="section">
            <div class="section-title">Diagnostic</div>
            <div class="diag-box">
                <div class="diag-label">Problème identifié</div>
                <div class="diag-value">{{ $diagnostic->type_probleme ?? 'Analyse complète' }}</div>
            </div>
            @if ($confidence)
            <div style="margin-top: 8px;">
                <span class="diag-label">Indice de confiance&nbsp;: </span>
                @php
                    $confidenceLower = strtolower((string) $confidence);
                    $confidenceClass = match(true) {
                        str_contains($confidenceLower, 'elev') || $confidenceLower === 'high' || $confidenceLower === 'élevé' => 'confidence-high',
                        str_contains($confidenceLower, 'moyen') || $confidenceLower === 'medium' => 'confidence-medium',
                        default => 'confidence-low',
                    };
                @endphp
                <span class="confidence-box {{ $confidenceClass }}">{{ $confidence }}</span>
            </div>
            @endif
        </div>

        <!-- ③ Plan d'action ordonné avec doses/produits -->
        @if (!empty($recommandations) && is_array($recommandations))
        <div class="section">
            <div class="section-title">Plan d'action ordonné</div>
            <table class="action-table">
                <tr>
                    <th class="step-num">#</th>
                    <th class="param-name">Paramètre</th>
                    <th class="measure-cell">Actuel</th>
                    <th class="measure-cell">Cible</th>
                    <th class="dose-cell">Dose / Produit</th>
                    <th class="note-cell">Note</th>
                </tr>
                @foreach ($recommandations as $i => $rec)
                    @if (is_array($rec) && (isset($rec['param']) || isset($rec['product'])))
                    <tr>
                        <td class="step-num">{{ $i + 1 }}</td>
                        <td class="param-name">{{ $rec['param'] ?? '' }}</td>
                        <td class="measure-cell">{{ $rec['current'] ?? '' }}</td>
                        <td class="measure-cell">{{ $rec['target'] ?? '' }}</td>
                        <td class="dose-cell">
                            @if (!empty($rec['dose'])){{ $rec['dose'] }}@endif
                            @if (!empty($rec['product']))<br><span style="font-size:11px; color:#6b7280;">{{ $rec['product'] }}</span>@endif
                        </td>
                        <td class="note-cell">{{ $rec['note'] ?? '' }}</td>
                    </tr>
                    @endif
                @endforeach
            </table>
        </div>
        @endif

        <!-- ④ Bloc sécurité ambre -->
        <div class="section">
            <div class="safety-block">
                <div class="safety-title">Précautions de sécurité</div>
                <div class="safety-text">
                    Ne jamais mélanger les produits chimiques entre eux.
                    Ajouter les produits directement dans l'eau (pas dans le skimmer)
                    en faisant tourner la filtration. Porter des protections (lunettes, gants)
                    lors de la manipulation. Respecter les délais d'attente entre les traitements.
                    En cas de doute ou de persistance du problème, contactez un professionnel agréé.
                </div>
            </div>
        </div>

        <!-- ⑤ Avertissement / disclaimer (DIAG-03 / SPEC Req 8) -->
        <div class="section">
            <div class="disclaimer-box">
                <div class="disclaimer-title">Avertissement — Conditions d'utilisation</div>
                <div class="disclaimer-text">
                    Ce diagnostic et le plan d'action associé sont fournis à titre indicatif uniquement,
                    sur la base des mesures saisies. Les résultats peuvent varier en fonction de la qualité
                    de l'eau source, des conditions climatiques, du type de piscine et d'autres facteurs
                    environnementaux propres à votre installation.<br><br>
                    Dlo Azur Piscines décline toute responsabilité quant aux dommages éventuels résultant
                    d'une utilisation incorrecte des produits recommandés. Ces recommandations ne remplacent
                    pas le jugement d'un professionnel qualifié. En cas de doute, faites appel à un pisciniste
                    certifié.<br><br>
                    En lançant ce diagnostic, vous avez reconnu avoir lu et accepté ces conditions le
                    {{ $diagnostic->disclaimer_accepted_at ? $diagnostic->disclaimer_accepted_at->format('d/m/Y') : 'la date du diagnostic' }}.
                </div>
            </div>
        </div>

    </div><!-- /.body -->

    <!-- Pied de page -->
    <div class="footer">
        <strong>Pierre ADAM — Dlo Azur Piscines</strong> · Martinique<br>
        WhatsApp&nbsp;: 0696 94 00 54 · <a href="https://dloazurpiscines.com" style="color: #8eb8d0;">dloazurpiscines.com</a><br>
        <div class="professional-notice">Ce document ne remplace pas l'avis d'un professionnel.</div>
    </div>

</div><!-- /.wrapper -->
</body>
</html>
