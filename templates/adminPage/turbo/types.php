<?php
$types = ['post' => 'Posts', 'page' => 'Pages'];
$typesArr = explode(';', $args['typesPost']);
foreach ($typesArr as &$type) {
    $type = !empty($types[$type]) ? $types[$type] : $type;
}

$typesIncludes = ['exclude' => 'Все таксономии, кроме исключенных', 'include' => 'Только указанные таксономии'];
?>

<div class="element-separator most">Типы записей: <b><?php echo implode(', ', $typesArr); ?></b></div>
<div class="element-separator">Включить в RSS:
    <b><?php echo !empty($typesIncludes[$args['typesIncludes']]) ? $typesIncludes[$args['typesIncludes']] : ''; ?></b></div>
<?php if (!empty($args['typesIncludes']) == 'exclude'): ?>
    <div class="element-separator">Таксономии для исключения: <b><?php echo $args['typesTaxExcludes']; ?></b></div>
<?php elseif (!empty($args['typesIncludes']) == 'include'): ?>
    <div class="element-separator">Таксономии для добавления: <b><?php echo $args['typesTaxIncludes']; ?></b></div>
<?php endif; ?>
<div class="element-separator most">Типы записей: <b><?php echo implode(', ', $typesArr); ?></b></div>
