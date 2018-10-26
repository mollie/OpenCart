<script>
    <?php
    foreach ($variables as $key => $val) {
        echo "var " . $key . "=" . $val . ";" . PHP_EOL;
    }
    ?>
</script>