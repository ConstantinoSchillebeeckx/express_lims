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




<!-- edit modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content panel-warning">
      <div class="modal-header panel-heading">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-pencil-square-o text" aria-hidden="true"></i> Edit item</h4>
      </div>
      <div class="modal-body">
        Editing the item <span id="editID"></span>
      </div>
      <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button type="button" class="btn btn-warning" id="confirmEdit">Save changes</button>
      </div>
    </div>
  </div>
</div>



<!-- delete modal -->
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









<!-- add row modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content panel-info">
            <div class="modal-header panel-heading">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-plus" aria-hidden="true"></i> Add item</h4>
            </div>
            <form class="form-horizontal" id='addItemForm' onsubmit="addItem()">
                <div class="modal-body">
                        <? forEach($fields as $field) {
                            $field_class = $db->get_field($table, $field); ?>
                            <div class="form-group">
                            <?php if ($field_class->is_required()) {
                                echo '<label class="col-sm-2 control-label">' . $field . '<span class="required">*</span></label>';
                            } else {
                                echo '<label class="col-sm-2 control-label">' . $field . '</label>';
                            } ?>
                            <div class="col-sm-10">
                            <?php if ( $field_class->is_fk() ) {  // if field is an fk, show a select dropdown with available values
                                    get_fks_as_select($field_class);
                            } else {
                                if ( in_array( $field_class->get_type(), array('timestamp', 'date') ) ) {
                                    echo '<input type="text" name="' . $field . '" class="form-control" disabled><span class="text-muted">This field has been disabled since this field type populates automatically.</span>'; // automatically filled
                                } elseif ($field_class->is_required()) {
                                    echo '<input type="text" name="' . $field . '" class="form-control" required>';
                                } else {
                                    echo '<input type="text" name="' . $field . '" class="form-control">';
                                }
                            } ?>
                            </div>
                            </div>
                        <?php } ?>
                    <p class="text-right"><span class="required">*</span> field is required</p>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn" data-dismiss="modal">Cancel</a>
                    <button type="submit" class="btn btn-info" id="confirmAddItem">Add item</button>
                </div>
            </form>
        </div>
    </div>
</div>


