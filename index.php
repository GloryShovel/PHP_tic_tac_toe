<html>
<head>
<style>
table {
    border-collapse: collapse;
}
table, th, td {
    border: 3px solid black;
}
</style>
</head>

<body>
<h1>Witaj w grze kółko i krzyżyk</h1>
<h3>Zasady:</h3>
<br>1.Krzyżyk zawsze zaczyna.
<br>2.Można cofnąć ruch tylko za zgodąobu graczy (klikając guzik cofnięcia strony).
<br>3.Grać można tylko na jednym komputerze oraz gracze grają na zmiane.
<br>4.Jeżeli znaczek nie pojawia się odrazu na planszy należy chwilkę odczekać (wina serwera).

<?php
/********************************************************
FUNCTIONS
********************************************************/
function clear_board(){
    //clears DB
    include("polaczenie.php");
    for($y=0; $y<=4; $y++){
        for($x=0; $x<=4; $x++){
            $DB->exec("Update Projekt_WPR Set value=0 Where x=$x And y=$y");
        }
    }
    $DB = Null;
}

function turn_setter($turn, $update_resutl){
    //function for setting value of turn based on begin/actions of duel
    if(isset($turn) && $update_resutl == true){
        $turn++;
        return $turn;
    }else if(isset($turn) && $update_resutl == false){
        return $turn;
    }else{
        return 0;
    }
}


function validate($play_ground, $x, $y, $value){
    //validates by checking row (for more info ask programist)
    //first half
    if($play_ground[$y][$x-1] == $value){
        if($play_ground[$y][$x-2] == $value){
            return true;
        }else if($play_ground[$y][$x+1] == $value){
            return true;
        }
    }

    if($play_ground[$y-1][$x-1] == $value){
        if($play_ground[$y-2][$x-2] == $value){
            return true;
        }else if($play_ground[$y+1][$x+1] == $value){
            return true;
        }
    }

    if($play_ground[$y-1][$x] == $value){
        if($play_ground[$y-2][$x] == $value){
            return true;
        }else if($play_ground[$y+1][$x] == $value){
            return true;
        }
    }

    if($play_ground[$y-1][$x+1] == $value){
        if($play_ground[$y-2][$x+2] == $value){
            return true;
        }else if($play_ground[$y+1][$x-1] == $value){
            return true;
        }
    }

    //second half
    if($play_ground[$y][$x+1] == $value && $play_ground[$y][$x+2] == $value){
        return true;
    }
    if($play_ground[$y+1][$x+1] == $value && $play_ground[$y+2][$x+2] == $value){
        return true;
    }
    if($play_ground[$y+1][$x] == $value && $play_ground[$y+2][$x] == $value){
        return true;
    }
    if($play_ground[$y+1][$x-1] == $value && $play_ground[$y+2][$x-2] == $value){
        return true;
    }
    
    //wining condition wasn't found
    return false;
}

function update_board($x, $y, $value){
    //updates board, catch return for function result (true - updated DB / false - didn't update)
    include ("polaczenie.php");

    //prevents changing claimed tile
    $result = $DB->query("Select value From Projekt_WPR Where x=$x And y=$y");
    $row = $result->fetch();
    if($row[0] == 0){
        //updates board in DB
        $DB->exec("Update Projekt_WPR Set value=$value Where x=$x And y=$y");
        $DB = null;
        return true;
    }else{
        $DB = null;
        return false;
    }
}

function draw_board($play_ground, $turn, $update_result){
    //generates html table to play on
    echo "<table>";
    for($y = 0; $y <= 2; $y++){
        echo "<tr>";
        for($x = 0; $x <= 2; $x++){
            if($play_ground[$y][$x] == 1){
                echo "<td><a href=\"index.php?y=$y&x=$x&turn=".turn_setter($turn, $update_result)."\"><img src=\"krzyzyk.jpg\" alt=\"Krzyżyk\"></a></td>";
            }else if($play_ground[$y][$x] == 2){
                echo "<td><a href=\"index.php?y=$y&x=$x&turn=".turn_setter($turn, $update_result)."\"><img src=\"kolko.jpg\" alt=\"Kółko\"></a></td>";
            }else{
                echo "<td><a href=\"index.php?y=$y&x=$x&turn=".turn_setter($turn, $update_result)."\"><img src=\"puste.jpg\" alt=\"Pusto\"></a></td>";
            }
        }
        echo "</tr>";
    }
    echo "</table>";
}

function draw_win_board($play_ground){
    echo "<table>";
    for($y = 0; $y <= 2; $y++){
        echo "<tr>";
        for($x = 0; $x <= 2; $x++){
            if($play_ground[$y][$x] == 1){
                echo "<td><img src=\"krzyzyk.jpg\" alt=\"Krzyżyk\"></a></td>";
            }else if($play_ground[$y][$x] == 2){
                echo "<td><img src=\"kolko.jpg\" alt=\"Kółko\"></a></td>";
            }else{
                echo "<td><img src=\"puste.jpg\" alt=\"Pusto\"></a></td>";
            }
        }
        echo "</tr>";
    }
    echo "</table>";
}

/********************************************************
LOGIC
********************************************************/
//Variables
$play_ground[3][3] = array();
$turn = $_GET['turn'];
    //gets x, y if set
if(isset($_GET['x']) || isset($_GET['y'])){
    $updated_x = $_GET['x'];
    $updated_y = $_GET['y'];
    //value equals 1-cross 2-circle
    $value = ($turn % 2)+1;
    //as well as updating board
    $update_result = update_board($updated_x, $updated_y, $value);
}

//clearing board if begining
if(!isset($turn)){
    clear_board();
}

//Downloading playgound from DB
include("polaczenie.php");
$query = $DB->query("Select x, y, value From Projekt_WPR");
while($table = $query->fetch()){
    $play_ground[$table[1]][$table[0]] = $table[2];
}
$DB = Null;

//drawing board for every update
//draw_board($play_ground, $turn, $update_result);

//validates
$validate_result = validate($play_ground, $updated_x, $updated_y, $value);
if($validate_result == true && $value == 1){
    draw_win_board($play_ground);
    echo "
        <h2>Mecz wygrywa krzyżyk!</h2><br>
        <a href=\"index.php\">Następna runda</a>
         ";
}else if($validate_result == true && $value == 2){
    draw_win_board($play_ground);
    echo "
        <h2>Mecz wygrywa kółko!</h2><br>
        <a href=\"index.php\">Następna runda</a>
         ";
}else{
    draw_board($play_ground, $turn, $update_result);
}


/********************************************************
DEV SECTION
********************************************************/
//TODO: done :) (now: tests)

/*
echo "
<form action=\"\" method=\"post\">
<input type=\"submit\" name=\"draw\" value=\"Rysuj\">
<input type=\"submit\" name=\"reset\" value=\"Resetuj\">
</form>
";

if(isset($_POST['draw'])){
    //handles Rozpocznij button
    draw_board($play_ground, $turn);
}

if(isset($_POST['reset'])){
    //handles Resetuj button
    clear_board();
    header("Location: index.php");
}
*/

?>
</body>
</html>