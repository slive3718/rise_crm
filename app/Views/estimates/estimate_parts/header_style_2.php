<table class="header-style" style="font-size: 13.5px;">
    <tr class="invoice-preview-header-row">
        <td class="invoice-info-container invoice-header-style-two" style="width: 40%; vertical-align: top;"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "estimate_info" => $estimate_info
            );
            echo view('estimates/estimate_parts/estimate_info', $data);
            ?>
        </td>
        <td class="hidden-invoice-preview-row" style="width: 20%;"></td>
        <td style="width: 40%; vertical-align: top; height:150px">
            <?php //echo view('estimates/estimate_parts/company_logo'); ?>
        </td>
    </tr>
    <tr>
        <td style="padding: 9%;"></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td style="width: 35%;"></td>
        <td colspan="2" style="width: 80%; height:30px; font-size:35px "><h6>JOB ESTIMATE</h6></td>
    </tr>
    <tr>
        <td style="width: 15%"></td>
        <td style="width: 35%">
        <?php
            echo view('estimates/estimate_parts/estimate_to', $data);
            ?>
        </td>
        <td style="width: 10%"></td>
        <td>
            <?php
            echo view('estimates/estimate_parts/estimate_from', $data);
            ?>
        </td>
    </tr>
</table>