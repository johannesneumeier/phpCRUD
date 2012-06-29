
<table class="autocrud-table autocrud-listing">
    <tr>
        <?php foreach ($this->fields as $field): ?>
        <?php if ($this->visibleListingField($field['Field'])): ?>
        <th>
            <div class="autocrud-col-heading">
                <?php echo $this->displayFieldName($field['Field']); ?>
            </div>
            <div class="autocrud-col-sorting-icons">
                <a class="autocrud-button autocrud-button-desc" href="?order-by=<?php echo $field['Field']; ?>&order-dir=desc">&darr;</a>
                <a class="autocrud-button autocrud-button-asc" href="?order-by=<?php echo $field['Field']; ?>&order-dir=asc">&uarr;</a>
            </div>
            <?php if (in_array($field['Field'], array_keys($this->options['description']))): ?>
                <?php echo $this->options['description'][$field['Field']]; ?>
            <?php endif; ?>
        </th>
        <?php endif; ?>
        <?php endforeach; ?>
        
        <?php foreach ($this->settings['allowedActions'] as $action): if ($action !== 'create'): ?>
        <th><?php echo $action; ?></th>
        <?php endif; endforeach; ?>
    </tr>
    
    <?php 
    $items = $this->getData();
    foreach ($items as $row): 
    ?>
    <tr>
        <?php foreach ($row as $fieldName => $data): ?>
        <?php if ($this->visibleListingField($fieldName)): ?>
        <td><span class="autocrud-cellwrapper"><?php echo $this->displayFieldData($fieldName, $data); ?></span></td>
        <?php endif; ?>
        <?php endforeach; ?>  
                  
                  
        <?php foreach ($this->settings['allowedActions'] as $action): if ($action !== 'create'): ?>
        <td>
            <form method="get" action="<?php echo $this->formAction; ?>">
                <input type="hidden" name="action" value="<?php echo $action; ?>" />
                <input type="hidden" name="primary" value="<?php echo $row[$this->primaryField]; ?>" />
                <?php echo $this->hiddenGets; ?>
                <button class="autocrud-button autocrud-button-<?php echo $action; ?>"><?php echo $action; ?></button>                
            </form>
        </td>
        <?php endif; endforeach; ?>
        
    </tr>
    <?php endforeach; ?>    
</table>