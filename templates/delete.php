
<table>
    <?php foreach ($this->fields as $key => $field): ?>
    <tr>
        <th><?php echo $field['Field']; ?></th>
        <td><?php echo $data[$field['Field']]; ?></td>
    </tr>
    <?php endforeach; ?>
</table>