<div id="modal-form-edit" class="modal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
   			<form id="Add_New_Country" name="Add_New_Country" action="" method="post" enctype="multipart/form-data">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="blue bigger">Please fill the following fields</h4>
			</div>

			<div class="modal-body overflow-visible">
				<div class="row">
					<div class="col-xs-12 col-sm-5">
						<div class="space"></div>
						<input type="file" id="cn_flag" name="cn_flag"/>
					</div>

					<div class="col-xs-12 col-sm-7">
						<div class="form-group">
							<label for="form-field-username">Country Name</label>
							<div>
                                <input id="cn_name" name="cn_name" class="input-large" type="text" placeholder="Country Name" value="" />
                            </div>
                        </div>

                        <div class="space-4"></div>

                        <div class="form-group">
                            <label for="form-field-username">Currency Code</label>

							<div>
                                <input id="cn_currency" name="cn_currency" class="input-medium" type="text" placeholder="Currency Code" value="" />
                            </div>
                        </div>

                        <div class="space-4"></div>

                        <div class="form-group">
                            <label for="form-field-first">Phone Code</label>
                            <div>
                                <input id="cn_ph" name="cn_ph" class="input-medium" type="text" placeholder="Phone Code" value="" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-sm" data-dismiss="modal">
                    <i class="icon-remove"></i>
                    Cancel
                </button>

                <button class="btn btn-sm btn-primary" type="button" onclick="validCountry();">
                    <i class="icon-ok"></i>
                    Save
                </button>
            </div>
            </form>
        </div>
    </div>
</div>