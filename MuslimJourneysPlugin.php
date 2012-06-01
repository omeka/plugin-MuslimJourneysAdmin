<?php


class MuslimJourneysPlugin extends Omeka_Plugin_Abstract
{

    protected $_filters = array('admin_items_form_tabs');

    protected $_hooks = array('after_save_form_item', 'admin_theme_header');

    public function hookAdminThemeHeader() {
        queue_css('muslim_journeys');
    }

    public function hookAfterSaveFormItem($item, $post)
    {
        $relatedPropId = record_relations_property_id(DCTERMS, 'relation');
        if(!$relatedPropId) {
            throw new Exception('Property with that id not found');
        }
        foreach($post['muslim_journeys_relations'] as $objectId) {
            $rel = new RecordRelationsRelation();
            $rel->subject_id = $item->id;
            $rel->subject_record_type = 'Item';
            $rel->object_id = $objectId;
            $rel->object_record_type = 'Item';
            $rel->property_id = $relatedPropId;
            $rel->save();
        }
    }

    public function filterAdminItemsFormTabs($tabs, $item)
    {
        $type = get_db()->getTable('ItemType')->find($item->item_type_id);
        if(!($type->name == 'Book' || $type->name == 'Essay')) {
            $tabs['Essays and Books'] = $this->getItemRelationsForm($item);
        }
        return $tabs;
    }


    public function getItemRelationsForm($item)
    {
        $itemTable = get_db()->getTable('Item');
        $books = $itemTable->findBy(array('type'=>'Book'));
        $essays = $itemTable->findBy(array('type'=>'Essay'));

        $booksCheckboxes = $this->itemsToCheckboxes($books, 'Books');
        $bookValues = mj_get_related_items('Book', $item);
        $bookIds = array();
        foreach($bookValues as $book) {
            $bookIds[] = $book->id;
        }
        $booksCheckboxes->setValue($bookIds);

        $essaysCheckboxes = $this->itemsToCheckboxes($essays, 'Essays');
        $essayValues = mj_get_related_items('Essay', $item);
        $essayIds = array();
        foreach($essayValues as $essay) {
            $essayIds[] = $essay->id;
        }
        $essaysCheckboxes->setValue($essayIds);


        $form = "";
        $form .= "<div id='mj-books'>";
        $form .= $booksCheckboxes;
        $form .= "</div>";
        $form .= "<div id='mj-essays'>";
        $form .= $essaysCheckboxes;
        $form .= "</div>";
        return htmlspecialchars_decode( $form );
    }

    private function itemsToCheckboxes($items, $itemType)
    {
        $array = array();
        foreach($items as $item) {
            $array[$item->id] = item('Dublin Core', 'Title', array(), $item);
        }


        $checkboxes = new Zend_Form_Element_MultiCheckbox('muslim_journeys_relations',
            array('label'=>$itemType));
        $checkboxes->setAttribs(array('class'=>'mj-input'));
        $checkboxes->setSeparator("<div style='clear: both; margin-top: 12px'></div>");
        $checkboxes->setMultioptions($array);


        return $checkboxes;
    }

}