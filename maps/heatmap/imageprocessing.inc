<?php

function GetTransparantImage(){
    // Create a blank image 
    $tile = imagecreatetruecolor(256, 256);
    $transparant = imagecolorallocatealpha($tile, 0, 0, 0, 127);
    imagesavealpha($tile, true);
    imagefill($tile, 0, 0, $transparant);

    return $tile;
}

//gets the tile image or returns makes a new one
function GetOrCreateTile($x, $y, $z, $mapID){
    // check if file aready exists
    $fileName = "/var/www/data/heatmaptiles/$z/$x/$y/$mapID.png";
    if (file_exists($fileName)){
        return imagecreatefrompng($fileName);
    }

    // Create a blank image 
    $tile = GetTransparantImage();

    //Tile text for debug
    //  $red = imagecolorallocatealpha($tile, 127, 0, 0, 0);
    //  $font = './whitrabt.ttf';
    //  imagettftext($tile, 20, 0, 10, 20, $red, $font, "x:$x,\n y:$y,\n z:$z");
    
    //save image
    mkdir("/var/www/data/heatmaptiles/$z/$x/$y/", 0777, true);
    imagepng($tile, $fileName);

    return $tile;
}

function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
{
    /* this way it works well only for orthogonal lines
    imagesetthickness($image, $thick);
    return imageline($image, $x1, $y1, $x2, $y2, $color);
    */
    if ($thick == 1) {
        return imageline($image, $x1, $y1, $x2, $y2, $color);
    }
    $t = $thick / 2 - 0.5;
    if ($x1 == $x2 || $y1 == $y2) {
        return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
    }
    $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
    $a = $t / sqrt(1 + pow($k, 2));
    $points = array(
        round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
        round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
        round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
        round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
    );
    imagefilledpolygon($image, $points, 4, $color);
    return imagepolygon($image, $points, 4, $color);
}

function DrawLineOnTile($p1, $p2){
    [$x_1, $y_1] = LatLngZToXY($p1[0], $p1[1], 17);
    [$x_2, $y_2] = LatLngZToXY($p2[0], $p2[1], 17);

    $xTile = floor($x_2);
    $yTile = floor($y_2);

    $xPixel_1 = round(($x_1 - $xTile) * 256);
    $xPixel_2 = round(($x_2 - $xTile) * 256);
    $yPixel_1 = round(($y_1 - $yTile) * 256);
    $yPixel_2 = round(($y_2 - $yTile) * 256);

    $tile = GetTransparantImage();// GetOrCreateTile($xTile, $yTile, 17, $mapID);
    $addition = GetTransparantImage();
    $translucentWhite = imagecolorallocatealpha($tile, 256, 256, 256, 0);
    imagelinethick($tile, $xPixel_1, $yPixel_1, $xPixel_2, $yPixel_2, $translucentWhite, 3);

    // imagecopymerge($tile, $addition, 0,0,0,0,64,64, 100);
    imagepng($tile, "/var/www/data/heatmaptiles/17/$xTile/$yTile/$mapID.png");

    return $tile;
}

function LatLngZToXY($lat,$lng, $z){
    //convert to radians
    $latRAD = deg2rad($lat);
    $lngRAD = deg2rad($lng);

    //Mercator projection
    $x = (1 / (2 * M_PI)) * pow(2, $z) * (M_PI + $lngRAD);
    $y = (1 / (2 * M_PI)) * pow(2, $z) * (M_PI - log(tan(M_PI / 4 + $latRAD / 2)));

    return [$x, $y];
}


?>