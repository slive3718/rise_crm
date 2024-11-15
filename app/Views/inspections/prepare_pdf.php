<div >
    <!-- Header Section -->
    <table width="100%" style="margin-top: 15px; border-collapse: collapse; font-size: 14px; ">
        <tr>
            <td width="100%" style="font-weight: bold; padding: 0  20px">
                <!-- Replace this with an inline base64 image for TCPDF -->
                <img src="<?=base_url('assets/images/inspection_logo.png')?>" style="width: 200px; height: auto;">
            </td>
        </tr>
        <tr>
            <td colspan="2" width="100%" style="font-weight: bold; padding: 0  20px;">
                <strong style="font-size:25px"><?= $template['template_name']?></strong>
            </td>
        </tr>
        <tr>
            <td width="66%" style="font-weight: bold; padding: 0  20px">
                <span style="font-size:15px"><?= date( "F d Y", strtotime($inspection['created_at']))?></span>
            </td>
            <td width="33%" style="font-weight: bold; padding: 0  20px">
                Incomplete
            </td>
        </tr>
    </table>

    <!-- Score Section -->
    <table width="100%" style="margin-top: 15px; border-collapse: collapse; font-size: 14px; ">
        <tr style="background-color: #e9edf6; ">
            <td width="33%" style="font-weight: bold; padding: 0  20px">
                <strong style="text-align: left">Score</strong> <span style="text-align: right; right:0; float:right; letter-spacing: 4px"> <?= countItemsValue($sections) .'/'. countAllFields($sections)?> <?= intVal( (countItemsValue($sections)/ countAllFields($sections))*100)?>% </span>
            </td>
            <td width="33%" style="font-weight: bold; padding: 0  20px">
                <strong style="text-align: left">Flagged items </strong> <span style="text-align: right; right:0; float:right"> <?= countFlaggedItems($sections) ?></span>
            </td>
            <td width="33%" style="font-weight: bold; padding: 0 20px">
                Actions
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-top: 15px; border-collapse: collapse; font-size: 14px; ">
        <tr>
            <td width="70%" style="font-weight: bold; padding: 0  20px">
                <strong style="text-align: left">Customer</strong>
            </td>
            <td width="30%" style=" padding: 0  20px">
                <span style="text-align: right;  float:right;"> <?= $inspection_client->company_name?> </span>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 0  20px">
                <strong style="text-align: left">Email</strong>
            </td>
            <td style=" padding: 0  20px">
                <span style="text-align: right; right:0; float:right;  padding: 0  20px"><?= $inspection_client->email?>  </span>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 0  20px">
                <strong style="text-align: left">Location</strong>
            </td>
            <td style=" padding: 0  20px">
                <span style="text-align: right; right:0; float:right;"><?= $inspection_client->address?>  </span>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 0  20px">
                <strong style="text-align: left">Conducted on</strong>
            </td>
            <td style=" padding: 0  20px">
                <span style="text-align: right; right:0; float:right;"><?= $inspection['inspection_date']?>  </span>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 0  20px">
                <strong style="text-align: left">Prepared By: </strong>
            </td>
            <td style=" padding: 0  20px">
                <span style="text-align: right; right:0; float:right;"><?= $inspection['inspector_name']?>  </span>
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 0  20px">
                <strong style="text-align: left">Paid By: </strong>
            </td>
            <td style=" padding: 0  20px">
                <span style="text-align: right; right:0; float:right;"> Test</span>
            </td>
        </tr>
    </table>



    <!-- Details Section -->

    <table width="100%" style="margin-top: 15px; border-collapse: collapse; font-size: 14px; ">
        <?php  foreach ($sections as $section_name => $fields): ?>
            <tr>
                <td width="70%"  style="font-weight: bold; color: #555; background-color: #e9edf6;  padding: 0  20px">
                    <strong><?= htmlspecialchars($section_name)?></strong>
                </td>
                <td width="30%" style="text-align: right;  padding: 0  20px; background-color:#e9edf6">
                    <strong><?= (countFlaggedItemsPerSection($sections[$section_name])) != 0 ?? ''?> Flagged</strong>
                </td>
            </tr>

            <?php foreach ($fields as $field): if(is_array($field) && $field['field_label']): ?>

                <tr style="border-bottom: 1px solid black">
                    <td width="70%" style="font-weight: bold; color: #555;  padding: 0  20px">
                        <?= htmlspecialchars($field['field_label']) ?>
                    </td>

                    <?php
                    $optionColor = '';
                    $optionColor = calculateFlags($field)
                    ?>
                    <td width="30%" style="text-align: right; padding: 0  20px; <?= $optionColor ?>">
                        <?php
                        // Handle different field types
                        if ($field['field_type'] == 'radio') {
                            // Display the selected radio option
                            foreach (json_decode($field['field_options'], true) as $option) {
                                if ($field['value'] == $option['label']) {
                                    echo htmlspecialchars($option['label']);
                                }
                            }
                        } else {
                            // For text, textarea, and select fields, display the value directly
                            echo htmlspecialchars(isset($field['value']) ? $field['value'] : '');
                        }
                        ?>
                    </td>
                </tr>
            <?php endif; endforeach; ?>
        <?php endforeach; ?>
    </table>
</div>


<?php

function calculateFlags($field)
{
    if ($field['field_type'] == 'radio') {
        foreach (json_decode($field['field_options'], true) as $option) {
            if ($field['value'] == $option['label']) {
                $optionColor = 'color: white; background-color: ' . ((isset($option['flags']) && $option['flags'] === '1') ? 'rgb(198,0,34)' : $option['color']) . '; ';
                return $optionColor;
            }
        }
    }
}

function countFlaggedItems($data) {
    $flaggedCount = 0;
    foreach ($data as $section) {
        foreach ($section as $item) {
            if (isset($item['flagged']) && $item['flagged'] == 1) {
                $flaggedCount++;
            }
        }
    }
    return $flaggedCount;
}

function countFlaggedItemsPerSection($data) {
    $flaggedCount = 0;
    foreach ($data as $item) {
        if (isset($item['flagged']) && $item['flagged'] == 1) {
            $flaggedCount++;
        }
    }
    return $flaggedCount;
}

function countItemsValue($data) {
    $countItemsValue = 0;

    foreach ($data as $section) {
        foreach ($section as $item) {
            if ($item['value']) {
                $countItemsValue++;
            }
        }
    }
    return $countItemsValue;
}

function countAllFields($data) {
    $fieldsCount = 0;
    foreach ($data as $section) {
        $fieldsCount += count($section);
    }
    return $fieldsCount;
}
?>