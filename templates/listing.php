
<table class="autocrud-table autocrud-listing">
    <tr>
        <?php foreach ($this->fields as $field): ?>
        <?php if ($this->visibleListingField($field['Field'])): ?>
        <th>
            <?php echo $this->displayFieldName($field['Field']); ?>
            <a href="?order-by=<?php echo $field['Field']; ?>&order-dir=desc">&darr;</a>
            <a href="?order-by=<?php echo $field['Field']; ?>&order-dir=asc">&uarr;</a>
        </th>
        <?php endif; ?>
        <?php endforeach; ?>
        <th>edit</th>
        <th>delete</th>
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
        <td><a href="?action=edit&primary=<?php echo $row[$this->primaryField]; ?>">edit</a></td>
        <td><a href="?action=delete&primary=<?php echo $row[$this->primaryField]; ?>">delete</a></td>
    </tr>
    <?php endforeach; ?>    
</table>