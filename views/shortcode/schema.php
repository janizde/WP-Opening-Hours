<?php

extract($this->data['attributes']);

/**
 * Variables defined by extraction
 *
 * @var       $schema     array   Associative array containing JSON-LD schema
 */
?>
<script type="application/ld+json">
  <?php echo json_encode($schema, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES); ?>
</script>
