
<table class="autocrud-table autocrud-edit">
    <?php foreach ($this->fields as $key => $field): ?>
    <?php if (!in_array($field['Field'], $this->options['hidden'])) : ?>
    <tr>
        <th>
            <?php echo $this->displayFieldName($field['Field']); ?>
            <?php if (in_array($field['Field'], array_keys($this->options['description']))): ?>
                <?php echo $this->options['description'][$field['Field']]; ?>
            <?php endif; ?>            
        </th>
        <td>
            <?php 
            $name = $field['Field'];
            $val  = $this->displayFieldData($name, $data[$name]);
            preg_match('/[a-z]\(([0-9]+)\)/i', $field['Type'], $lengthMatches);
            $length = !empty($lengthMatches[1]) ? ' size="' . $lengthMatches[1] . '" ' : NULL;
                        
            $disabled = '';
            if ($field['Key'] === 'PRI' || in_array($name, $this->options['disabled'])) {
                $disabled = ' disabled="disabled" ';
            }
            
            $dateClass = '';
            if ($field['Type'] == 'date') {
                $dateClass = ' class="autocrud-datepicker" ';
            } else if ($field['Type'] == 'timestamp') {
                $dateClass = ' class="autocrud-datetimepicker" ';
            }
            
            if ($this->fieldOption($name, 'select')) {
                echo '<select name="' . $name . '">';
                echo '<option value="unselected">select</option>';
                foreach ($this->options['select'][$name] as $option) {
                    $selected = ($val == $option) ? ' selected="selected" ' : '';
                    echo '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
                }
                echo '</select>';
            } elseif ($this->fieldOption($name, 'radio')) {
                foreach ($this->options['radio'][$name] as $value => $label) {
                    echo '<label><input type="radio" name="' . $name . '" value="' . $value . '"';
                    if ($val == $value) {
                        echo ' checked="checked" ';
                    }
                    echo '>' . $label . '</label>';
                }
            } elseif ($this->fieldOption($name, 'upload')) {
                echo '<input type="text" name="' . $name . '" value="' . $val . '" disabled="disabled" />';
                echo '<input type="file" name="' . $name . '-file" />';
            } else {
            
                switch ($field['Type']) {
                    
                    case 'text':
                        echo '<textarea name="' . $name . '" ' . $disabled . '>' . $val . '</textarea>';
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