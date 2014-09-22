<?php
/*
 * Copyright © 2005-2006, FileMaker, Inc. All rights reserved.
 * NOTE: Use of this source code is subject to the terms of the FileMaker
 * Software License which accompanies the code. Your use of this source code
 * signifies your agreement to such license terms and conditions. Except as
 * expressly granted in the Software License, no other copyright, patent, or
 * other intellectual property license or right is granted, either expressly or
 * by implication, by FileMaker.
 */
require_once dirname(__FILE__) . '/../../FileMaker.php';

$fm =& new FileMaker();
$req =& $fm->newFindRequest('test');
$req->addFindCriterion('join','catty');
$result = $req->execute();
    if (FileMaker::isError($result)) {
	   echo $result->getMessage();

    }else{
	   list($record) = $result->getRecords();
	}


//$child =& $record->newRelatedRecord('test2changed');


//create child
/*
$child->setField('test2::name', 'CommitAddChildTest');
$result = $child->commit();
    if (FileMaker::isError($result)) {
        return $this->fail($result->getMessage());
    }
echo "done";
*/

/*
//edit child
// Get the first child record.
list($child) = $record->getRelatedSet('test2');

$child->setField('test2::name', 'CommitEditChildTest');
$result = $child->commit();
    if (FileMaker::isError($result)) {
        return $this->fail($result->getMessage());
    }
echo "done";
*/


//delete a child

$parent =& $fm->getRecordById('test', $record->getRecordId());
$children =& $parent->getRelatedSet('test2changed');


if (FileMaker::isError($children)) {
    echo $children->getMessage();
    exit ;
}

/* Runs through each of the children */
foreach ($children as $newchild) {

     // Examples of what can be done.
     //  echo $newchild->getRecordId();
     //  echo $child->getRecordId();
     //  echo $newchild->getField('test2::name');

}

    //Delete related record.
   $result =   $newchild->delete();

    if (FileMaker::isError($result)) {
        echo $result->getMessage();
    }

?>
