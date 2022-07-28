<?php
$json = file_get_contents('./restaurant1.json');
$json_data = json_decode($json,true);
$foods = $json_data['data'];
$interval1 = date_diff(DateTime::createFromFormat("U",$json_data['timestamp']),new DateTime());
$timeDifference1 = (new DateTime())->getTimestamp() - $json_data['timestamp'];

$json = file_get_contents('./restaurant2.json');
$json_data = json_decode($json,true);
$jedla = $json_data['data'];
$timeDifference2 = (new DateTime())->getTimestamp() - $json_data['timestamp'];

$json = file_get_contents('./restaurant3.json');
$json_data = json_decode($json,true);
$essen = $json_data['data'];
$timeDifference3 = (new DateTime())->getTimestamp() - $json_data['timestamp'];


if ($timeDifference1 > 1200) {

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,"https://www.delikanti.sk/prevadzky/3-jedalen-prif-uk/");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    $output = curl_exec($ch);
    $dom = new DOMDocument();
    @$dom->loadHTML($output);
    $dom->preserveWhiteSpace = false;
    $tables = $dom->getElementsByTagName('table');
    $rows = $tables->item(0)->getElementsByTagName('tr');
    $index = 0;
    $dayCount = 0;
    $foods = [];
    $foodCount = $rows->item(0)->getElementsByTagName('th')->item(0)->getAttribute('rowspan');

    foreach ($rows as $row) {
        if ($row->getElementsByTagName('th')->item(0)) {
            $foodCount = $row->getElementsByTagName('th')->item(0)->getAttribute("rowspan");
            $day = trim($rows->item($index)->getElementsByTagName('th')->item(0)->getElementsByTagName('strong')->item(0)->nodeValue);
            $th = $rows->item($index)->getElementsByTagName('th')->item(0);
            foreach ($th->childNodes as $node) {
                if (!($node instanceof \DOMText)) {
                    $node->parentNode->removeChild($node);
                }
            }
            $date = trim($rows->item($index)->getElementsByTagName('th')->item(0)->nodeValue);
            array_push($foods,["date" => $date, "day" => $day, "menu" => []]);

            for ($i = $index; $i < $index + intval($foodCount); $i++) {
                if ($foods[$dayCount]) {
                    array_push($foods[$dayCount]["menu"], trim($rows->item($i)->getElementsByTagName('td')->item(1)->nodeValue));
                }
            }
            $index += intval($foodCount);
            $dayCount++;
        }
    }
    $data = ["timestamp" => (new DateTime())->getTimestamp(), "data" => $foods];
    $fp = fopen('restaurant1.json','w');
    fwrite($fp,json_encode($data));
    fclose($fp);
}

if ($timeDifference2 > 1200) {
    $ch1 = curl_init();
    curl_setopt($ch1,CURLOPT_URL,"http://eatandmeet.sk/tyzdenne-menu");
    curl_setopt($ch1,CURLOPT_RETURNTRANSFER,1);
    $output = curl_exec($ch1);
    $dom1 = new DOMDocument();
    @$dom1->loadHTML($output);
    $dom1->preserveWhiteSpace = false;

    $parseNodes = ["day-1", "day-2", "day-3","day-4","day-5","day-6", "day-7"];

    $jedla = [
        ["date" => date('d.m.Y', strtotime('monday this week') ), "day" => "Pondelok", "menu" => [] ],
        ["date" => date('d.m.Y', strtotime('tuesday this week') ), "day" => "Utorok", "menu" => [] ],
        ["date" => date('d.m.Y', strtotime('wednesday this week') ), "day" => "Streda", "menu" => [] ],
        ["date" => date('d.m.Y', strtotime('thursday this week') ), "day" => "Štvrtok", "menu" => [] ],
        ["date" => date('d.m.Y', strtotime('friday this week') ), "day" => "Piatok", "menu" => [] ],
        ["date" => date('d.m.Y', strtotime('saturday this week') ), "day" => "Sobota", "menu" => [] ],
        ["date" => date('d.m.Y', strtotime('sunday this week') ), "day" => "Nedeľa", "menu" => [] ],
    ];

    foreach ($parseNodes as $index => $nodeId) {

        $node = $dom1->getElementById($nodeId);

        foreach ($node->childNodes as $menuItem){
            if ($menuItem && $menuItem->childNodes->item(1) && $menuItem->childNodes->item(1)->childNodes->item(3)){
                $nazov = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(1)->childNodes->item(1)->nodeValue);
                $cena = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(1)->childNodes->item(3)->nodeValue);
                $popis = trim($menuItem->childNodes->item(1)->childNodes->item(3)->childNodes->item(3)->nodeValue);
                array_push($jedla[$index]["menu"],"$nazov ($popis): $cena");

            }
        }
    }

    $data2 = ["timestamp" => (new DateTime())->getTimestamp(), "data" => $jedla];
    $fp = fopen('./restaurant2.json','w');
    fwrite($fp,json_encode($data2));
    fclose($fp);
}


if ($timeDifference3 > 1200) {
    $ch2 = curl_init();
    curl_setopt($ch2,CURLOPT_URL,"http://www.freefood.sk/menu/");
    curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
    $output = curl_exec($ch2);
    $dom2 = new DOMDocument();
    @$dom2->loadHTML($output);
    $dom2->preserveWhiteSpace = false;
    $essen = [];

    $div = $dom2->getElementById('fiit-food');
    $ul = $div->getElementsByTagName('div')->item(0)->getElementsByTagName('ul')->item(0);
    $dayCount=-1;
    foreach ($ul->childNodes as $li){
        array_push($essen,["date" => $date,"day" => $day, "menu" => []]);
        foreach ($li->childNodes->item(1)->childNodes as $nextUl) {
            $nazov = $nextUl->childNodes->item(1)->nodeValue;
            $cena = trim($nextUl->childNodes->item(2)->nodeValue);
            array_push($essen[$dayCount]["menu"], "$nazov, $cena");
        }
        $dayCount++;
    }

    $data3 = ["timestamp" => (new DateTime())->getTimestamp(), "data" => $essen];
    $fp = fopen('./restaurant3.json','w');
    fwrite($fp,json_encode($data3));
    fclose($fp);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jedalne</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body onload="start()">
<div class="container">
    <div id="mainBar">
        <div>Jedalne menu</div>
        <div></div>
        <div>
            <div id="h-0"><?php
                echo $foods[0]['date'];
                ?>
            </div>
            <div id="h-1"><?php
                echo $foods[1]['date'];
                ?>
            </div>
            <div id="h-2"><?php
                echo $foods[2]['date'];
                ?>
            </div>
            <div id="h-3"><?php
                echo $foods[3]['date'];
                ?>
            </div>
            <div id="h-4"><?php
                echo $foods[4]['date'];
                ?>
            </div>
            <div id="h-5"><?php
                echo $foods[5]['date'];
                ?>
            </div>
            <div id="h-6"><?php
                echo $jedla[6]['date'];
                ?>
            </div>
            <div id="h-7"><?php
                echo $foods[1]['date'];
                echo " - ";
                echo $jedla[6]['date'];
                ?>
            </div>
        </div>
    </div>
    <nav>
        <button class="sportMenu" onclick="pondelok()">Pondelok</button>
        <button class="sportMenu" onclick="utorok()">Utorok</button>
        <button class="sportMenu" onclick="streda()">Streda</button>
        <button class="sportMenu" onclick="stvrtok()">Stvrtok</button>
        <button class="sportMenu" onclick="piatok()">Piatok</button>
        <button class="sportMenu" onclick="vsetko()">Vsetky</button>
    </nav>
    <div id="leftSidebar">
        <div class="container2">
            Delikanti<br><br>
            <div id="d-0">
                <?php
                echo "<span>";
                echo $foods[0]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 6 ; $i++){
                    echo "<p> $i:  ";
                    echo $foods[0]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="d-1">
                <?php
                echo "<span>";
                echo $foods[1]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 6 ; $i++){
                    echo "<p> $i:  ";
                    echo $foods[1]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="d-2">
                <?php
                echo "<span>";
                echo $foods[2]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 6 ; $i++){
                    echo "<p> $i:  ";
                    echo $foods[2]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="d-3">
                <?php
                echo "<span>";
                echo $foods[3]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 6 ; $i++){
                    echo "<p> $i:  ";
                    echo $foods[3]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="d-4">
                <?php
                echo "<span>";
                echo $foods[4]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 6 ; $i++){
                    echo "<p> $i:  ";
                    echo $foods[4]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="d-5">
                <?php
                echo "<span>";
                echo $foods[5]["day"];
                echo "</span>";
                ?>
            </div>
            <div id="d-6">
                <?php
                echo "<span>";
                echo $jedla[6]["day"];
                echo "</span>";
                ?>
            </div>
        </div>
    </div>
    <main>
        <div class="container2">
            Eat & Meet<br>
            <div id="e-0">
                <?php
                echo "<span>";
                echo $jedla[0]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 10 ; $i++){
                    echo "<p> ";
                    echo $jedla[0]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="e-1">
                <?php
                echo "<span>";
                echo $jedla[1]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 10 ; $i++){
                    echo "<p>  ";
                    echo $jedla[1]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="e-2">
                <?php
                echo "<span>";
                echo $jedla[2]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 10 ; $i++){
                    echo "<p>  ";
                    echo $jedla[2]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="e-3">
                <?php
                echo "<span>";
                echo $jedla[3]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 10 ; $i++){
                    echo "<p>  ";
                    echo $jedla[3]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="e-4">
                <?php
                echo "<span>";
                echo $jedla[4]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 10 ; $i++){
                    echo "<p> ";
                    echo $jedla[4]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="e-5">
                <?php
                echo "<span>";
                echo $jedla[5]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 10 ; $i++){
                    echo "<p>";
                    echo $jedla[5]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="e-6">
                <?php
                echo "<span>";
                echo $jedla[6]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 10 ; $i++){
                    echo "<p> ";
                    echo $jedla[6]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
        </div>
    </main>
    <div id="rightSidebar">
        <div class="container2">
            Fiit Food<br><br>
            <div id="f-0">
                <?php
                echo "<span>";
                echo $foods[0]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 4 ; $i++){
                    echo "<p> $i:  ";
                    echo $essen[0]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="f-1">
                <?php
                echo "<span>";
                echo $foods[1]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 4 ; $i++){
                    echo "<p> $i:  ";
                    echo $essen[1]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="f-2">
                <?php
                echo "<span>";
                echo $foods[2]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 4 ; $i++){
                    echo "<p> $i:  ";
                    echo $essen[2]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="f-3">
                <?php
                echo "<span>";
                echo $foods[3]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 4 ; $i++){
                    echo "<p> $i:  ";
                    echo $essen[3]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="f-4">
                <?php
                echo "<span>";
                echo $foods[4]["day"];
                echo "</span>";
                for ($i = 0 ; $i < 4 ; $i++){
                    echo "<p> $i:  ";
                    echo $essen[4]["menu"][$i];
                    echo "</p>";
                }?>
            </div>
            <div id="f-5">
                <?php
                echo "<span>";
                echo $jedla[5]["day"];
                echo "</span>";
                ?>
            </div>
            <div id="f-6">
                <?php
                echo "<span>";
                echo $jedla[6]["day"];
                echo "</span>";
                ?>
            </div>
        </div>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>

