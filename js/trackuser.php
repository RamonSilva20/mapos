<?php
echo("chegou");
class TrackUser{
	function SaveScreenshot(){

			$user_log_array = array(
				'Celso Torok'=>'Celso Torok',
				'Rafael Marques'=>'Rafael Marques',
				'Jose Marques'=>'Jose Marques',
			);

			$event_log_array = array(
				'click'=>' has clicked on the Web Page',
				'form-submit'=>' has submitted Form Submit.',
				'form-clear'=>' has reset the form data.',
				'link-click'=>' has clicked the link.',
				'right-click'=>' has clicked Right Button of the Mouse.',
				'copy'=>' has copied web page content.',
			);
				
			$filteredData=substr($_POST['image_code'], strpos($_POST['image_code'], ",")+1);

			//Decode the string
			$unencodedData=base64_decode($filteredData);

			//Save the image
			$file_path="/home/computadorpc/public_html/os/screenshot";

			$filename = date("Y-m-d-H-i-s").".png";

			file_put_contents($file_path."/".$filename, $unencodedData);

			//SAVE EVENT LOG
			$file_content = @file_get_contents("/home/computadorpc/public_html/os/screenshot/event-log.log");
			$file_content.="\n ".$user_log_array[$_POST['user_name']].$event_log_array[$_POST['event_name']].". Image : ".$filename.". Time : ".date("Y-m-d-H-i-s").". IP Address : ".$_SERVER['REMOTE_ADDR'];

			file_put_contents("/home/computadorpc/public_html/os/screenshot/event-log.log", $file_content);


	}//end function

}

$TrackUser  = new TrackUser();
if(isset($_REQUEST['image_code']) && $_REQUEST['image_code']!="")
{
	$TrackUser->SaveScreenshot();
}
?>
