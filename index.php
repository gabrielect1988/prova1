<?php
session_start();
date_default_timezone_set('Europe/Rome');

// Giorni in italiano
$giorni_settimana = [
    'Monday'    => 'Lunedì',
    'Tuesday'   => 'Martedì',
    'Wednesday' => 'Mercoledì',
    'Thursday'  => 'Giovedì',
    'Friday'    => 'Venerdì',
    'Saturday'  => 'Sabato',
    'Sunday'    => 'Domenica'
];

$oggi_eng = date("l");
$oggi_ita = $giorni_settimana[$oggi_eng];
$oggi_data = date("Y-m-d");

$orario_entrata = $_SESSION['entrate'][$oggi_data] ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["orario"])) {
    $_SESSION['entrate'][$oggi_data] = $_POST["orario"];
    $orario_entrata = $_POST["orario"];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Calcolo Ore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap + Font -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Inter', sans-serif;
        }

        .card {
            max-width: 100%;
            margin: 0 auto;
            border: none;
            border-radius: 12px;
        }

        h1 {
            font-size: 1.6rem;
            margin-bottom: 0.5rem;
        }

        .info-block {
            font-size: 1rem;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .countdown {
            font-weight: 600;
        }

        .form-control {
            font-size: 1rem;
            padding: 0.6rem 0.75rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        @media (min-width: 768px) {
            .card {
                max-width: 500px;
            }
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1>👋 Ciao Gabriele</h1>
            <p class="text-muted mb-3">Oggi è: <strong><?= $oggi_ita . ' ' . date("d/m/Y - H:i") ?></strong></p>

            <form method="post" class="mb-3">
                <label for="orario" class="form-label mb-1">A che ora sei entrato?</label>
                <input type="time" id="orario" name="orario" class="form-control mb-2" required value="<?= $orario_entrata ?? '' ?>">
                <button type="submit" class="btn btn-primary w-100">Calcola</button>
            </form>
<p style="text-align: center;"><a class="btn btn-success" href="https://gadaquila.ct.ws/timbrature/calendario/" role="button">Visualizza Calendario</a></p>
            <?php
            if ($orario_entrata) {
                $ora_corrente = time();
                $ora_entrata_timestamp = strtotime($oggi_data . " " . $orario_entrata);

                $ore_da_fare = 0;
                $pausa_minuti = 0;

                if (in_array($oggi_eng, ['Monday', 'Wednesday', 'Friday'])) {
                    $ore_da_fare = 6;
                } elseif (in_array($oggi_eng, ['Tuesday', 'Thursday'])) {
                    $ore_da_fare = 9;
                    $pausa_minuti = 31;
                } else {
                    echo "<div class='alert alert-warning mt-2'><strong>⚠️ Oggi è {$giorni_settimana[$oggi_eng]}</strong><br>Nessuna ora lavorativa prevista.</div>";
                    exit;
                }

                $secondi_da_lavorare = ($ore_da_fare * 3600) + ($pausa_minuti * 60);
                $tempo_di_uscita = $ora_entrata_timestamp + $secondi_da_lavorare;
                $secondi_mancanti = $tempo_di_uscita - $ora_corrente;
                $uscita_orario = date("H:i", $tempo_di_uscita);

                if ($secondi_mancanti <= 0) {
                    echo "<div class='alert alert-success mt-3'><strong>✅ Hai già finito oggi!</strong><br>🎉 Bravo!</div>";
                } else {
                    echo "<div class='alert alert-success mt-3'>";
                    echo "<div class='info-block'>🕓 <strong>Ingresso:</strong> $orario_entrata</div>";
                    echo "<div class='info-block'>🚪 <strong>Uscita prevista:</strong> $uscita_orario</div>";
                    echo "<div class='info-block'>⏳ <strong>Tempo rimanente:</strong> <span class='countdown' id='countdown'></span></div>";
                    echo "</div>";

                    echo "<script>
                        const uscitaTimestamp = {$tempo_di_uscita} * 1000;

                        function aggiornaCountdown() {
                            const now = new Date().getTime();
                            const distanza = uscitaTimestamp - now;

                            if (distanza <= 0) {
                                document.getElementById('countdown').innerHTML = 'Tempo completato! 🎉';
                                clearInterval(interval);
                                return;
                            }

                            const ore = Math.floor(distanza / (1000 * 60 * 60));
                            const minuti = Math.floor((distanza % (1000 * 60 * 60)) / (1000 * 60));
                            const secondi = Math.floor((distanza % (1000 * 60)) / 1000);

                            document.getElementById('countdown').innerHTML =
                                ore + 'h ' + minuti + 'm ' + secondi + 's';
                        }

                        aggiornaCountdown();
                        const interval = setInterval(aggiornaCountdown, 1000);
                    </script>";
                }
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
