<div><b><?php echo app_lang("bill_to"); ?></b></div>
<?php if (get_setting("invoice_style") != "style_3") { ?>
    <div class="b-b" style="line-height: 2px; border-bottom: 1px solid #f2f4f6;"> </div>
<?php } ?>
<strong><?php echo $client_info->company_name; ?> </strong>
<div style="line-height: 1px;"> </div>
<span class="invoice-meta text-default">
    <?php if ($client_info->address || $client_info->vat_number || (isset($client_info->custom_fields) && $client_info->custom_fields)) { ?>
        <div><?php echo nl2br($client_info->address ? $client_info->address : ""); ?>
            <?php if ($client_info->city) { ?>
                <br /> <?php echo $client_info->city; ?>,
            <?php } ?>
            <?php if ($client_info->state) { ?> 
                <?php echo $client_info->state; ?>
            <?php } ?>
            <?php if ($client_info->zip) { ?>
                <?php echo $client_info->zip; ?>
            <?php } ?>
            <?php if ($client_info->phone) { ?>
                <br /><?php echo $client_info->phone; ?>
            <?php } ?>
            <?php if ($client_info->vat_number || $client_info->gst_number) { ?>
                <?php if ($client_info->vat_number) { ?>
                    <br /><?php echo app_lang("vat_number") . ": " . $client_info->vat_number; ?>
                <?php } else { ?>
                    <br /><?php echo app_lang("gst_number") . ": " . $client_info->gst_number; ?>
                <?php } ?>
            <?php } ?>
            <?php
            if (isset($client_info->custom_fields) && $client_info->custom_fields) {
                foreach ($client_info->custom_fields as $field) {
                    if ($field->value) {
                        echo "<br />" . $field->custom_field_title . ": " . view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value));
                    }
                }
            }
            ?>


        </div>
    <?php } ?>
</span>