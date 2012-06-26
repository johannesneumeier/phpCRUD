
<table class="autocrud-table autocrud-edit">
    <?php foreach ($this->fields as $key => $field): ?>
    <?php if (!in_array($field['Field'], $this->hidden)) : ?>
    <tr>
        <th><?php echo $this->displayFieldName($field['Field']); ?></th>
        <td>
            <?php 
            //print_r($field);
            $name = $field['Field'];
            $val  = $this->displayFieldData($name, $data[$field['Field']]);
            preg_match('/[a-z]\(([0-9]+)\)/i', $field['Type'], $lengthMatches);
            $length = !empty($lengthMatches[1]) ? ' size="' . $lengthMatches[1] . '" ' : NULL;
                        
            $disabled = '';
            if ($field['Key'] === 'PRI' || in_array($field['Field'], $this->disabled)) {
                $disabled = ' disabled="disabled" ';
            }
            
            print_r($field);
            
            $dateClass = '';
            if ($field['Type'] == 'date') {
                $dateClass = ' class="autocrud-datepicker" ';
            } else if ($field['Type'] == 'timestamp') {
                $dateClass = ' class="autocrud-datetimepicker" ';
            }
            
            if (in_array($field['Field'], array_keys($this->select))) {
                echo '<select name="' . $name . '">';
                echo '<option value="unselected">select</option>';
                foreach ($this->select[$field['Field']] as $option) {
                    echo '<option value="' . $option . '"';
                    if ($val == $option) {
                        echo ' selected="selected" ';
                    }
                    echo '>' . $option . '</option>';
                }
                echo '</select>';
            } elseif (in_array($field['Field'], array_keys($this->radio))) {
                foreach ($this->radio[$field['Field']] as $value => $label) {
                    echo '<label><input type="radio" name="' . $field['Field'] . '" value="' . $value . '"';
                    if ($val == $value) {
                        echo ' checked="checked" ';
                    }
                    echo '>' . $label . '</label>';
                }
            } elseif (in_array($field['Field'], array_keys($this->upload))) {
                echo '<input type="text" name="' . $name . '" value="' . $val . '" disabled="disabled" />';
                echo '<input type="file" name="' . $name . '-file" />';
            } else {
            
                switch ($field['Type']) {
                    
                    case 'text':
                        echo '<textarea name="' . $name . '">' . $val . '</textarea>';
                        break;
                    
                    default:
                        if (empty($dateClass)) {
                            echo '<input type="text" name="' . $name . '" value="' . $val . '" ' . $disabled . $length . ' />';
                        } else {
                            // for date/time fields add a datepicker and a hidden field with the mysql-formatted datetime value
                            echo '<input type="hidden" name="' . $name . '" value="' . $val . '" ' . $disabled . ' />';
                            echo '<input type="text" name="' . $name . '-picker" value="' . $val . '" ' . $disabled . $length . $dateClass . ' />';
                        }
                        break;
                        
                }
            }
 
            ?>
        </td>
    </tr>
    <?php endif; ?>
    <?php endforeach; ?>
</table>