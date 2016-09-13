<!-- history modal -->
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content panel-info">
      <div class="modal-header panel-heading">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-history" aria-hidden="true"></i> Item history</h4>
      </div>
      <div class="modal-body">
        History for item <span id="historyID"></span>
      </div>
    </div>
  </div>
</div>




<!-- edit item modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content panel-warning">
            <div class="modal-header panel-heading">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-pencil-square-o text" aria-hidden="true"></i> Edit item</h4>
            </div>
            <form class="form-horizontal" id='editItemForm' onsubmit="editItem()">
                <div class="modal-body">
                    <p class="lead">Editing the item <span id="editID"></span></p>
                    <?php get_form_table_row($table); // vars defined by build_table() in EL.php ?>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn" data-dismiss="modal">Cancel</a>
                    <button type="button" class="btn btn-warning" id="confirmEdit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>









<!-- delete item modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content panel-danger">
      <div class="modal-header panel-heading">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-times" aria-hidden="true"></i> Archive item</h4>
      </div>
      <div class="modal-body">
        Are you sure you want to archive the item <span id="deleteID"></span>?
      </div>
      <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button type="button" class="btn btn-danger" id="confirmDelete">Archive item</button>
      </div>
    </div>
  </div>
</div>

















<!-- add item modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content panel-primary">
            <div class="modal-header panel-heading">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Add item</h4>
            </div>
            <form class="form-horizontal" id='addItemForm' onsubmit="addItem()">
                <div class="modal-body">
                        <?php get_form_table_row($table); // vars defined by build_table() in EL.php ?>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn" data-dismiss="modal">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="confirmAddItem">Add item</button>
                </div>
            </form>
        </div>
    </div>
</div>


