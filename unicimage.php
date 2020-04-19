<?php 

function image($source)
{
    // получаем картинку по указанному адресу
    $image = file_get_contents($source);
    $im    = new Imagick();
    //Imagick::readImageBlob — Reads image from a binary string
    $im->readimageblob($image);

    $width  = $im->getImageWidth();
    $height = $im->getImageHeight();

    // определяем какая из сторон больше
    if ($height < $width) {
        $biggest_side = $width;
        $smaller_side = $height;
    }
    else {
        $biggest_side = $height;
        $smaller_side = $width;
    }
    if ($biggest_side / $smaller_side <3) {

        // задаём угол поворота
        $angle = rand(0, 1)?.2:-.2;

        $im->rotateImage('#ffffff', $angle);
        // вычисляем составные части наименьшей стороны 
        $smaller_side_x1 = $biggest_side*sin(deg2rad(abs($angle)));
        $smaller_side_x2 = $smaller_side*cos(deg2rad(abs($angle)));

        // фактические размеры после поворота
        $fact_new_width =  $im->getImageWidth();
        $fact_new_height =  $im->getImageHeight();

        if ($height < $width) {
            $fact_new_biggest_side = $fact_new_width;
            $fact_new_smaller_side = $fact_new_height;
        }
        else {
            $fact_new_biggest_side = $fact_new_height;
            $fact_new_smaller_side = $fact_new_width;
        }

        // вычисляемый размер после поворота
        $calc_new_smaller_side = $smaller_side_x1 + $smaller_side_x2;
        
        // отношение высоты полезной части картинки к высоте с незаполненными областями
        $ratio = ( $calc_new_smaller_side - 2 * $smaller_side_x1  ) / $fact_new_smaller_side ;

        // увеличаваем картинку
        if ($height < $width) {
            $im->scaleImage(floor($fact_new_biggest_side / $ratio), floor($fact_new_smaller_side / $ratio));
        } else {
            $im->scaleImage(floor($fact_new_smaller_side / $ratio), floor($fact_new_biggest_side / $ratio));
        }

        $width_arter_scale =  $im->getImageWidth();
        $height_arter_scale =  $im->getImageHeight();

        // обрезаем от картинки части, которые указывали на поворот
        // получаем итоговое изображение равное по размерам изначальному
        $im->cropImage($width, $height, floor(($width_arter_scale - $width)/2-1), floor(($height_arter_scale - $height)/2-1) );
    }

    // меняем яркость и контрастность
    $im->brightnessContrastImage(rand(-5, 5), rand(-5, 5));

    $image = $im->getimageblob();

    // Пишет данные в файл (куда, что)
    $result = file_put_contents($source, $image); 

    $im -> clear();

    return $result;
}


function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags); 
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
     $files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}
// получили массив со списком всех картинок
$cdir = rglob ("./*.{jpg,png}",GLOB_BRACE); //эта маска - не регулярка!

echo "<pre>";
print_r($cdir);
echo "</pre>";

// обходим массив и получаем имена файлов
$i = 0;
foreach( $cdir as $path) {
    
    echo "<div class='error".$i."'>".$_SERVER['DOCUMENT_ROOT']. substr($path,1) ." ...error</div>";
    
    try {
        $result = image($_SERVER['DOCUMENT_ROOT'].substr($path,1));

        // если функция вернула true, то файл перезаписан
        if ($result) {
            echo "<div>".$_SERVER['DOCUMENT_ROOT'].substr($path,1) . "  - success</div>";
            echo "<style>.error".$i."{display:none;}</style>";
        } else {
            throw new Exception();
        }
    }
    catch (Exception $ex) {
        //Выводим сообщение об исключении.
        echo $ex->getMessage();
    }

    $i++;
}

echo "<h1>ГОТОВО</h1>";

?>