<select name="<?php echo $name; ?>" id="<?php echo $name; ?>" <?php if ($class) {
    echo 'class="' . $class . '"';
} ?>>
    <?php
    foreach ($options as $option) {
        ?>
        <option <?php if ($value == $option["value"]) {
            echo "selected";
        } ?> value="<?php echo $option["value"]; ?>">
            <?php echo $option["text"]; ?>
        </option>
    <?php } ?>
</select>