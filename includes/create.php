
<table>
    <?php foreach ($this->fields as $key => $field): ?>
    <tr>
        <th><?php echo $field['Field']; ?></th>
        <td>
            <?php 
            //print_r($field);
            $val  = $data[$field['Field']];
            $name = $field['Field'];
            $disabled = $field['Key'] === 'PRI' ? ' disabled="disabled" ' : '';
            
            
            switch ($field['Type']) {
                
                case 'text':
                    echo '<textarea name="' . $name . '">' . $val . '</textarea>';
                    break;
                
                default:
                    echo '<input type="text" name="' . $name . '" value="' . $val . '" ' . $disabled . ' />';
                    break;
                    
            }
 
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>