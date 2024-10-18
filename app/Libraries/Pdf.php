<?php

namespace App\Libraries;

require_once APPPATH . "ThirdParty/tcpdf/tcpdf.php";

class Pdf extends \TCPDF {

    private $pdf_type;

    public function __construct($pdf_type = '') {
        parent::__construct();

        $this->pdf_type = $pdf_type;
        $this->SetFontSize(10);
    }

    public function Header() {
        if ($this->pdf_type == 'invoice' || $this->pdf_type == 'estimate') {
            $break_margin = $this->getBreakMargin();
            $auto_page_break = $this->AutoPageBreak;
            $this->SetAutoPageBreak(false, 0);

            $img_file = get_file_from_setting("invoice_pdf_background_image", false, get_setting("timeline_file_path"));
            $this->Image($img_file, 0, 0, 210, 297, '', '', '', false, 500, '', false, false, 0);

            // $img_file2 = (base_url().'assets/images/footer_bg.png');
            // $imageHeight = 80; // Set the desired height
            // $imageWidth = 210;  // Full width of A4 page in mm
            
            // // Add the image to the PDF
            // $this->Image($img_file2, 0, 297 - $imageHeight, $imageWidth, $imageHeight, 'PNG', '', '', false, 300, '', false, false, 0);
            
            // restore auto-page-break status
            $this->SetAutoPageBreak($auto_page_break, $break_margin);
        } else {
            // call the original Header method from the parent class
            parent::Header();
        }
    }

    public function Footer() {
        if ($this->pdf_type == 'invoice' || $this->pdf_type == 'estimate') {
            // Check if this is the last page
        
            // Check if this is the last page
        
            // Define the image file path and dimensions
            $img_file2 = base_url().'assets/images/footer_bg.png';
            $imageHeight = 80; // Set the desired height
            $imageWidth = 210;  // Full width of A4 page in mm
            
            // Calculate the Y position for the image
            $yPosition = $this->getPageHeight() - $imageHeight;

            // Add the image to the PDF at the calculated position
            $this->Image($img_file2, 0, $yPosition, $imageWidth, $imageHeight, 'PNG', '', '', false, 300, '', false, false, 0);
         // Set the Y position for the text above the image
         $textYPosition = $yPosition - 40; // Adjust this value as needed

         // Set the position and add the text
         $this->SetXY(80, $textYPosition); // You can adjust the X position as needed
         $this->SetFont('', 'B', 25); 
         $this->Cell(0, 5, "THANK YOU ", 0, 0, 'C'); // Centered text
         $this->SetXY(80, $textYPosition); // You can adjust the X position as needed
         $this->Cell(0, 55, "FOR YOUR BUSINESS!", 0, 0, 'C'); // Centered text
        } else {
            // Call the original Footer method from the parent class for other types
            parent::Footer();
        }
    }

}
