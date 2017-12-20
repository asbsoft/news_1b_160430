<?php //echo __FILE__;
//var_dump($this->blocks);exit;
//$all=$this->viewBlocks->all();var_dump($all);exit;
?>
<!--
BEGIN:TEST-EXT-1B: This is FIRST parent<br>
-->
<h2><?= 'Main title @ news_1b_160430' ?></h2>

(1B)#1  This text BETWEEN BLOCKS will NOT be lost ...<br /><br />

(1B)Before block 'header'<br />
<?php $this->startBlock('header') ?>
   (1B)Start block 'header'...<br />
   <h3>Original 1B header</h3>
   ... (1B)stop block 'header'.<br /><br />
<?php $this->stopBlock('header') ?>
(1B)After block 'header'<br />

(1B)#2  This text BETWEEN BLOCKS will NOT be lost ...<br /><br />

(1B)Before block 'outer'<br />
<?php $this->startBlock('outer') ?>

    (1B)Start block 'outer'...<br />

    <?php $this->startBlock('inner') ?>
        (1B)Start block 'inner'...<br />
        NNN = <?= $n ?> (var @ 3B)<br />
        ...(1B)stop block 'inner'.<br /><br />
    <?php $this->stopBlock('inner') ?>

    ... (1B)stop block 'outer'.<br /><br />

<?php $this->stopBlock('outer') ?>
(1B)After block 'outer'<br />

(1B)#9  This text BETWEEN BLOCKS will NOT be lost ...<br /><br />
<!--
END:TEST-EXT-1B<br>
-->