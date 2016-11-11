<?php


echo  "here";
//include 'easyochem_functions.php';
//echo $user->get('name');





$row = 1;
if (($handle = fopen("muzyka.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        print_r($data);
        echo $data[6];
        $starting_gif_address = "images/".$data[1];
        $product_gif_address = "images/".$data[7];
        $reagent_gif_address = "images/".$data[4];
        $arrow = "images/arrow.gif";

        //echo $reagent_gif_address;

        $imagedata = getimagesize ($starting_gif_address);
        //echo $imagedata[0];


        $starting_img = imagecreatefromgif($starting_gif_address);
        $product_img = imagecreatefromgif($product_gif_address);
        $reagent_img = imagecreatefromgif($reagent_gif_address);


        //imagecopymerge($starting_img, $reagent_img, 10, 10, 0, 0, 100, 47, 75);
        //header('Content-Type: image/gif');
        //imagegif($starting_img);

        mergereagentandarrow($reagent_gif_address, $arrow, 'images/'.$row.'mergedreagent.gif');
//        merge($starting_gif_address, 'images/'.$row.'mergedreagent.gif', 'images/'.$row.'merged.gif');
        merge('images/'.$row.'mergedreagent.gif', $product_gif_address, 'images/'.$row.'merged.gif');
        //merge($starting_img, $reagent_img, 'images/merged.jpg');


       //get base64 of merged image
       $path = 'images/'.$row.'merged.gif';
       $type = pathinfo($path, PATHINFO_EXTENSION);
       $imgdata = file_get_contents($path);
       $base64 = 'data:image/' . $type . ';base64,' . base64_encode($imgdata);


       ////convert smiles answer to mdl mole file
        //$marvinjsconfig = get_config('qtype_easyonamejs_options');
        $descriptorspec = array(
           0 => array("pipe", "r"),  // Stdin is a pipe that the child will read from.
           1 => array("pipe", "w"),  // Stdout is a pipe that the child will write to.
           2 => array("pipe", "r") // Stderr is a file to write to.
        );
        $output = '';
        //echo $marvinjsconfig->obabelpath;
        //$command = escapeshellarg($marvinjsconfig->obabelpath . ' -imol -o' . $format . ' --title');
        $command = '/usr/bin/obabel' . ' -ismi -o' . 'mol' . ' --gen2d -d';

        $process = proc_open($command, $descriptorspec, $pipes);
        //print_object($process);
        if (is_resource($process)) {
            /* 0 => writeable handle connected to child stdin
               1 => readable handle connected to child stdout
               2 +> errors */
            //print_object($pipes);
            fwrite($pipes[0], $data[2]);
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $err = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            //echo $output;
            //echo $err;
            // It is important that you close any pipes before calling,
            // proc_close in order to avoid a deadlock.
            $returnvalue = proc_close($process);
        }


           $answer = trim('MDL Molfile INSERTED\n'.$output); 
        //}








        //echo moodle xml for question
        $moodlequestion='<question type="easyonamejs">
    <name>
      <text>Determine Product JM'.$row.'</text>
    </name>
    <questiontext format="html">
      <text><![CDATA[<p>Provide the starting compound for the following reaction?</p><p><br></p><p><img alt="Embedded Image" src="'.$base64.'" /><br></p>]]></text>
    </questiontext>
    <generalfeedback format="html">
      <text></text>
    </generalfeedback>
    <defaultgrade>1.0000000</defaultgrade>
    <penalty>0.3333333</penalty>
    <hidden>0</hidden>
    <answers><![CDATA[Array]]></answers>
    <answer fraction="100" format="moodle_auto_format">
      <text>'.$answer.'</text>
      <feedback format="html">
        <text></text>
      </feedback>
    </answer>
  </question>';




$file = 'jm_predict_starting_moodle.xml';
// Open the file to get existing content
$current = file_get_contents($file);
// Append a new person to the file
$current .= $moodlequestion;
// Write the contents back to the file
file_put_contents($file, $current);




//echo $moodlequestion;









        //echo "image";


        $row++;

        for ($c=0; $c < $num; $c++) {
            echo $data[$c] . "<br />\n";
        }

        //if ($row > 10) break;

    }
    fclose($handle);
}



/////   merge function



function merge($filename_x, $filename_y, $filename_result) {

 // Get dimensions for specified images

 list($width_x, $height_x) = getimagesize($filename_x);
 list($width_y, $height_y) = getimagesize($filename_y);

 // Create new image with desired dimensions


  if ($height_x > $height_y) {$height = $height_x;}else{$height = $height_y;}


 $image = imagecreatetruecolor($width_x + $width_y, $height);
$white = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $white);


 // Load images and then copy to destination image

 $image_x = imagecreatefromgif($filename_x);
 $image_y = imagecreatefromgif($filename_y);

 imagecopy($image, $image_x, 0, 0, 0, 0, $width_x, $height_x);
 imagecopy($image, $image_y, $width_x, 0, 0, 0, $width_y, $height_y);

 // Save the resulting image to disk (as JPEG)

 //imagejpeg($image, $filename_result);
 imagegif($image, $filename_result);

 // Clean up

 imagedestroy($image);
 imagedestroy($image_x);
 imagedestroy($image_y);

}



function mergereagentandarrow($filename_x, $filename_y, $filename_result) {

 // Get dimensions for specified images

 list($width_x, $height_x) = getimagesize($filename_x);
 list($width_y, $height_y) = getimagesize($filename_y);

 // Create new image with desired dimensions

 $image = imagecreatetruecolor($width_y, $height_x + $height_y);
$white = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $white);


 // Load images and then copy to destination image

 $image_x = imagecreatefromgif($filename_x);
 $image_y = imagecreatefromgif($filename_y);

 imagecopy($image, $image_x, ($width_y-$width_x)/2, 0, 0, 0, $width_x, $height_x);
 imagecopy($image, $image_y, 0, $height_x, 0, 0, $width_y, $height_y);

 // Save the resulting image to disk (as JPEG)

 imagegif($image, $filename_result);




 // Clean up

 imagedestroy($image);
 imagedestroy($image_x);
 imagedestroy($image_y);

}











//phpinfo();

//$mode="simplealkane";



//$smiles=createrandomsmiles($mode);



?>
