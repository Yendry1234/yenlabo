<?php
$proceso = false;
$contenido = array();
$tipoArchivo = "";

function procesarArchivo($nombreArchivo)
{
    if (isset($_FILES[$nombreArchivo]) && $_FILES[$nombreArchivo]["size"] > 0) {
        $archivo = $_FILES[$nombreArchivo]["tmp_name"];
        $tamanio = $_FILES[$nombreArchivo]["size"];
        $tipo = $_FILES[$nombreArchivo]["type"];
        $nombre = $_FILES[$nombreArchivo]["name"];

        $archi = fopen($archivo, "rb");
        $contenido = array();

        while (($linea = fgets($archi)) !== false) {
            $contenido[] = explode('&', $linea);
        }

        fclose($archi);

        $tipoArchivo = $nombreArchivo;
        return $contenido;
    }
    return null;
}

if (isset($_POST["oc_Control"])) {
    $nombreArchivo1 = "txtArchi1";
    $nombreArchivo2 = "txtArchi2";
    $contenido1 = procesarArchivo($nombreArchivo1);
    $contenido2 = procesarArchivo($nombreArchivo2);

    if ($contenido1 !== null && $contenido2 !== null) {
        $proceso = true;
    }
}

function determinarTipoDato($valor)
{
    return is_numeric($valor) ? "Entero" : "Cadena";
}

function determinarUso($valor)
{
    return is_numeric($valor) ? "Cuantitativo" : "Cualitativo";
}

function determinarValor($valor)
{
    if (is_numeric($valor)) {
        $valor = (int) $valor;
        return "0 a $valor";
    } else {
        return "Variado";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("segmentos/encabe.inc"); ?>
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.css">
    <title>Proceso de datos</title>
</head>

<body class="container">
    <header class="row">
        <?php include_once("segmentos/menu.inc"); ?>
    </header>
    <main class="row">
        <div class="linea_sep">
            <h3>Procesando archivos.</h3>
            <br>
            <?php
            if (!$proceso) {
                echo '<div class="alert alert-danger" role="alert">';
                echo '  Los archivos no pueden ser procesados, verifique sus datos.....!';
                echo '</div>';
            } else {
                echo "<h4>Datos Generales.</h4>";
                echo "<table class='table table-bordered table-hover'>
                        <tr>
                            <td>Nombre</td>
                            <td>Tipo</td>
                            <td>Peso</td>
                            <td>Observaciones</td>
                        </tr>
                        <tr>
                            <td>" . $_FILES["txtArchi1"]["name"] . "</td>
                            <td>" . $_FILES["txtArchi1"]["type"] . "</td>
                            <td>" . number_format(($_FILES["txtArchi1"]["size"] / 1024) / 1024, 2, '.', ',') . " MB</td>
                            <td>" . count($contenido1) . "</td>
                        </tr>
                        <tr>
                            <td>" . $_FILES["txtArchi2"]["name"] . "</td>
                            <td>" . $_FILES["txtArchi2"]["type"] . "</td>
                            <td>" . number_format(($_FILES["txtArchi2"]["size"] / 1024) / 1024, 2, '.', ',') . " MB</td>
                            <td>" . count($contenido2) . "</td>
                        </tr>
                    </table>";

                echo "<br>";

                echo "<table id='tblDatos1' class='table table-bordered table-hover'>
                        <h4>" . $_FILES["txtArchi1"]["name"] . "</h4>
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th>Tipo</th>
                                <th>Uso</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>";
                $campoIncremental1 = 1;
                foreach ($contenido1 as $i => $campos) {
                    foreach ($campos as $j => $valor) {
                        echo "<tr>
                                <td>Campo " . $campoIncremental1++ . "</td>
                                <td>" . determinarTipoDato($valor) . "</td>
                                <td>" . determinarUso($valor) . "</td>
                                <td>" . determinarValor($valor) . "</td>
                              </tr>";
                    }
                }
                echo "</tbody></table>";

                echo "<br>";

                if ($proceso) {
                    echo "<h4>" . $_FILES["txtArchi1"]["name"] . "</h4>";

                    $resumen = array(
                        'M' => array(),
                        'F' => array()
                    );

                    $provincias = array('San José', 'Cartago', 'Alajuela', 'Heredia', 'Puntarenas', 'Guanacaste', "Limón");
                    foreach ($provincias as $provincia) {
                        $resumen['M'][$provincia] = 0;
                        $resumen['F'][$provincia] = 0;
                    }

                    foreach ($contenido1 as $campos) {
                        $genero = $campos[1];
                        $provincia = $campos[6];

                        if ($genero === 'M' || $genero === 'F') {
                            $resumen[$genero][$provincia]++;
                        }
                    }

                    echo "<table class='table table-bordered table-hover'>";
                    echo "<thead><tr><th>Género</th>";
                    foreach ($provincias as $provincia) {
                        echo "<th>$provincia</th>";
                    }
                    echo "<th>Total</th></tr></thead><tbody>";

                    $totalM = 0;
                    $totalF = 0;

                    foreach (['M', 'F'] as $genero) {
                        echo "<tr><td>$genero</td>";
                        $totalGenero = 0;
                        foreach ($provincias as $provincia) {
                            echo "<td>{$resumen[$genero][$provincia]}</td>";
                            $totalGenero += $resumen[$genero][$provincia];
                        }
                        echo "<td>$totalGenero</td>";

                        if ($genero === 'M') {
                            $totalM = $totalGenero;
                        } else {
                            $totalF = $totalGenero;
                        }
                    }

                    echo "<tr><td>Observaciones</td>";
                    foreach ($provincias as $provincia) {
                        echo "<td></td>";
                    }
                    echo "<td>" . ($totalM + $totalF) . "</td>";

                    echo "</tbody></table>";
                }

                echo "<br>";

                if ($proceso) {
                    echo "<h4>" . $_FILES["txtArchi1"]["name"] . "</h4>";

                    if ($totalM == 0) $totalM = 1;
                    if ($totalF == 0) $totalF = 1;

                    $factor = 100 / ($totalM + $totalF);

                    echo "<table class='table table-bordered table-hover'>";
                    echo "<thead><tr><th>Género</th>";
                    foreach ($provincias as $provincia) {
                        echo "<th>$provincia</th>";
                    }
                    echo "<th>Total</th></tr></thead><tbody>";

                    foreach (['M', 'F'] as $genero) {
                        echo "<tr><td>$genero</td>";
                        foreach ($provincias as $provincia) {
                            $porcentaje = ($resumen[$genero][$provincia] * $factor);
                            echo "<td>" . number_format($porcentaje, 2) . "%</td>";
                        }
                        $totalGenero = $genero === 'M' ? $totalM : $totalF;
                        $totalPorcentaje = $totalGenero * $factor;
                        echo "<td>" . number_format($totalPorcentaje, 2) . "%</td>";
                    }

                    echo "<tr><td>Observaciones</td>";
                    foreach ($provincias as $provincia) {
                        echo "<td></td>";
                    }
                    $total = ($totalM + $totalF) * $factor;
                    echo "<td>" . number_format($total, 2) . "%</td>";

                    echo "</tbody></table>";
                }

                echo "<br>";

                if ($proceso) {
                    echo "<h4>" . $_FILES["txtArchi2"]["name"] . "</h4>";

                    $resumen2 = array(
                        'M' => array(),
                        'F' => array()
                    );

                    $provincias = array('San José', 'Cartago', 'Alajuela', 'Heredia', 'Puntarenas', 'Guanacaste', "Limón");
                    foreach ($provincias as $provincia) {
                        $resumen2['M'][$provincia] = 0;
                        $resumen2['F'][$provincia] = 0;
                    }

                    foreach ($contenido2 as $campos) {
                        $genero = $campos[1];
                        $provincia = $campos[6];

                        if ($genero === 'M' || $genero === 'F') {
                            $resumen2[$genero][$provincia]++;
                        }
                    }

                    echo "<table class='table table-bordered table-hover'>";
                    echo "<thead><tr><th>Género</th>";
                    foreach ($provincias as $provincia) {
                        echo "<th>$provincia</th>";
                    }
                    echo "<th>Total</th></tr></thead><tbody>";

                    $totalM2 = 0;
                    $totalF2 = 0;

                    foreach (['M', 'F'] as $genero) {
                        echo "<tr><td>$genero</td>";
                        $totalGenero2 = 0;
                        foreach ($provincias as $provincia) {
                            echo "<td>{$resumen2[$genero][$provincia]}</td>";
                            $totalGenero2 += $resumen2[$genero][$provincia];
                        }
                        echo "<td>$totalGenero2</td>";

                        if ($genero === 'M') {
                            $totalM2 = $totalGenero2;
                        } else {
                            $totalF2 = $totalGenero2;
                        }
                    }

                    echo "<tr><td>Observaciones</td>";
                    foreach ($provincias as $provincia) {
                        echo "<td></td>";
                    }
                    echo "<td>" . ($totalM2 + $totalF2) . "</td>";

                    echo "</tbody></table>";
                }

                echo "<br>";

                if ($proceso) {
                    echo "<h4>" . $_FILES["txtArchi2"]["name"] . "</h4>";

                    if ($totalM2 == 0) $totalM2 = 1;
                    if ($totalF2 == 0) $totalF2 = 1;

                    $factor = 100 / ($totalM2 + $totalF2);

                    echo "<table class='table table-bordered table-hover'>";
                    echo "<thead><tr><th>Género</th>";
                    foreach ($provincias as $provincia) {
                        echo "<th>$provincia</th>";
                    }
                    echo "<th>Total</th></tr></thead><tbody>";

                    foreach (['M', 'F'] as $genero) {
                        echo "<tr><td>$genero</td>";
                        foreach ($provincias as $provincia) {
                            $porcentaje = ($resumen2[$genero][$provincia] * $factor);
                            echo "<td>" . number_format($porcentaje, 2) . "%</td>";
                        }
                        $totalGenero2 = $genero === 'M' ? $totalM2 : $totalF2;
                        $totalPorcentaje2 = $totalGenero2 * $factor;
                        echo "<td>" . number_format($totalPorcentaje2, 2) . "%</td>";
                    }

                    echo "<tr><td>Observaciones</td>";
                    foreach ($provincias as $provincia) {
                        echo "<td></td>";
                    }
                    $total2 = ($totalM2 + $totalF2) * $factor;
                    echo "<td>" . number_format($total2, 2) . "%</td>";

                    echo "</tbody></table>";
                }
            }
            ?>
        </div>
    </main>
    <footer class="row pie">
        <?php include_once("segmentos/pie.inc"); ?>
    </footer>
    <script src="formatos/bootstrap/js/jquery-1.11.3.min.js"></script>
    <script src="formatos/bootstrap/js/bootstrap.js"></script>
    <script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#tblDatos1').dataTable({
                "language": {
                    "url": "dataTables.Spanish.lang"
                }
            });
        });
    </script>
</body>

</html>