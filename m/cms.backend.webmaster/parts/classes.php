<?php namespace qg;

$exp = (array)json_decode( G()->SET['cms']['classes_expose_css']->v , 1 );

if (isset($vars['class_expose_css'])) {
    $class = $vars['class_expose_css'];
    if ($vars['value']) $exp[$class] = 1;
    else unset($exp[$class]);
    G()->SET['cms']['classes_expose_css'] = json_encode($exp);
    exit;
}

?>
<?php if (isset($vars['class_detail'])) {
    $class = $vars['class_detail'];
    ?>
    <div style="padding:15px">
        <h2 style="display:flex">
            <div style="flex:auto">Tag "<?=hee($class)?>"</div>
            <button onclick="$fn('page::loadPart')(<?=$Cont?>, 'classes');">zur übersicht</button>
        </h2>
        <div>Seiten / Inhalte mit dieser Klasse</div>
    </div>
    <?php
    $sql =
    " SELECT * ".
    " FROM page p LEFT JOIN page_class pc ON p.id = pc.page_id ".
    " WHERE pc.class = ".D()->quote($class);
    ?>
    <table class=c1-style>
        <?php foreach (D()->query($sql) as $row) {
            $Page = Page($row['page_id']);
            ?>
            <tr>
                <td>
                    #<?=$Page?>
                <td>
                    <a href="<?=$Page->url()?>"><?=trim($Page->Title()) ? strip_tags($Page->Title()) : '(kein Titel)'?>  </a>
                    <div style="font-size:.8em">
                    <?php foreach ($Page->Path() as $P) { ?>
                        <a href="<?=$P->url()?>"><?=trim($P->Title()) ? strip_tags($P->Title()) : '(kein Titel)'?></a> >
                    <?php } ?>
                    </div>
        <?php } ?>
    </table>

<?php } else { ?>

    <table class=c1-style id=cmsClassesTable>
      <thead>
          <tr>
              <th> Tag
              <th> Benutzt
              <th> use as HTML-Class-Attribute
      <tbody style="cursor:pointer">
          <?php foreach (D()->query("SELECT *, count(class) as count FROM page_class GROUP BY class ORDER BY page_id = '".$Cont."', count(class)") as $vs) {
              $class = $vs['class'];
              $used[$class] = 1;
              ?>
              <tr itemid="<?=hee($class)?>">
                  <td> <?=hee($class)?>
                  <td class=-num_used> <?=$vs['count']?>
                  <td> <input name=expose_css value="<?=hee($class)?>" type=checkbox <?=isset($exp[$class])?'checked':''?>>
          <?php } ?>
          <?php foreach ($exp as $class => $egal) {
              if (isset($used[$class])) continue;
              ?>
              <tr>
                  <td> <?=hee($class)?>
                  <td class=-num_used> 0
                  <td> <input name=expose_css value="<?=hee($class)?>" type=checkbox <?=isset($exp[$class])?'checked':''?>>
          <?php } ?>
    </table>
    <script>
      $('#cmsClassesTable').on('change','[name=expose_css]', function() {
          $fn('page::loadPart')(<?=$Cont?>, 'classes', {class_expose_css:this.value, value:this.checked});
      })
      $('#cmsClassesTable > tbody').on('click', function(e) {
          var item = e.target.closest('tr').getAttribute('itemid');
          $fn('page::loadPart')(<?=$Cont?>, 'classes', {class_detail:item});
      })
    </script>
    <style>
    #cmsClassesTable > tbody {
        cursor:pointer;
    }
    #cmsClassesTable .-num_used {
        text-align:center;
    }
    </style>
<?php } ?>
