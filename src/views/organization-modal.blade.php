<div class="modal fade custom-modal" id="create-new-organization" tabindex="-1" role="dialog" aria-labelledby="create-new-organization" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="modal-close"  title="Close" onclick="closeModel('create-new-organization')">
                X
            </button>
            <div class="title-modal">
                <h2><span id="organizationTitle">Create New </span> Organization :-</h2>
            </div>
            <div class="content-modal">
                <form id="organizationForm" action="" method="POST" onsubmit="return false" > 
                    @csrf
                    <div id="organizationFormWrap"></div> 
                    <div class="btn-wrap right">
                        <button type="reset" class="btn btn-danger btn-sm" onclick="closeModel('create-new-organization')" title="Close">Cancel</button>
                        <button type="submit" id="submit_btn" class="btn-success btn-sm btn" title="Save">Save</button>
                    </div> 
                </form>
            </div>
        </div>
    </div>
</div>