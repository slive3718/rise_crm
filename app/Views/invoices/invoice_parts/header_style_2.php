<table class="header-style" style="font-size: 13.5px;">
    <tr class="invoice-preview-header-row">
        <td class="invoice-info-container invoice-header-style-two" style="width: 40%; vertical-align: top;"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "invoice_info" => $invoice_info
            );
            echo view('invoices/invoice_parts/invoice_info', $data);
            ?>
        </td>
        <td class="hidden-invoice-preview-row" style="width: 20%;"></td>
        <td style="width: 40%; vertical-align: top; height:150px">
          
        </td>
    </tr>
    <tr>
        <td style="padding: 5px;"></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td style="width: 43%;"></td>
        <td colspan="2" style="width: 80%; height:30px; font-size:35px "><h6>INVOICE</h6></td>
    </tr>
    <tr>
        <td style="width: 15%"></td>
        <td style="width: 35%">
            <?php
            echo view('invoices/invoice_parts/bill_to', $data);
            ?>
        </td>
        <td style="width: 5%"></td>
        <td>
            <?php
            echo view('invoices/invoice_parts/bill_from', $data);
            ?>
        </td>
    </tr>
</table>