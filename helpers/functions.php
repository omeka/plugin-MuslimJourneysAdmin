<?php
/*
Example theming usage:

    <?php $books = mj_get_related_items('Book');
          mj_set_related_items_for_loop($books);
    ?>
    <h2>Related Books</h2>
    <?php while(mj_loop_related_items()): ?>

    Notice that you can't just do item('Dublin Core', 'Title')!
        <p><?php echo item('Dublin Core', 'Title', array(), mj_get_current_related_item()); ?></p>
    <?php endwhile; ?>

 */

function mj_loop_related_items()
{
    $items = mj_get_related_items_for_loop();
    return loop_records('related_items', $items, "mj_set_current_related_item");
}

function mj_get_current_related_item()
{
    $view = __v();
    return $view->related_item;
}

function mj_set_current_related_item($item)
{
    $view = __v();
    $view->previous_related_item = $view->related_item;
    $view->related_item = $item;
}

function mj_set_related_items_for_loop($items)
{
    $view = __v();
    $view->related_items = $items;
}

function mj_get_related_items_for_loop()
{
    $view = __v();
    return $view->related_items;
}

function mj_get_related_items($itemType, $item = null)
{
    if(!$item) {
        $item = get_current_item();
    }
    $itemId = $item->id;
    $relatedPropId = record_relations_property_id(DCTERMS, 'relation');
    $params = array(
        'subject_id'=>$itemId,
        'subject_record_type'=>'Item',
        'property_id'=>$relatedPropId,
        'object_record_type'=>'Item'
    );

    $items = get_db()->getTable('RecordRelationsRelation')->findObjectRecordsByParams($params, array(), array('type'=>$itemType));
    return $items;
}

