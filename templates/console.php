<div class="wrap">
    <h1></h1>
    <?php settings_errors();?>
</div>
<div class="main-content-container container">
    <div class="page-header row no-gutters py-4">
        <div class="col">
            <h3 class="page-title">Image4io Console</h3>

        </div>
        <div class="col d-flex">
            <div class="d-inline-flex ml-auto my-auto">
               Test
            </div>
        </div>
    </div>
</div>
 <!--FOLDERS -->
 <div class="row">
    <div class="col">
        <span class="file-manager__group-title text-uppercase text-light">Folders</span>
    </div>
</div>

<div class="card card-small mb-3">
    <div class="row no-gutters p-2">
        <div class="col-lg-3 mb-2 mb-lg-0">
        </div>
        <div class="col">
            <div class="d-flex ml-lg-auto my-auto">
                <div class="btn-group btn-group-sm mr-2 ml-lg-auto" role="group" aria-label="Table row actions">
                    <button type="button" class="btn btn-white" onclick="DeleteFolders()">
                        <i class="far fa-trash-alt"></i>
                    </button>
                </div>
                <button class="btn btn-sm btn-accent d-inline-block ml-auto ml-lg-0" data-toggle="modal" data-target="#addNewFolderModal">
                    <i class="fas fa-plus"></i> Add New
                </button>
            </div>
        </div>
    </div>
</div>
<div class="row">
   <!-- 
        if ( == 0)
        {
            <div class="col-lg-3">
                <div class="file-manager__item--directory card card-small mb-3">
                    <div class="card-footer">
                        <h6 class="file-manager__item-title">@Localizer["There is no folder"]</h6>
                    </div>
                </div>
            </div>
        }
        
        else
        {
            foreach (var folder in Model.Folders)
            {
                <div class="col-lg-3">
                    <div class="file-manager__item file-manager__item--directory card card-small mb-3" ondblclick="window.location.href = '@(Context.Request.Path + "?path=" + folder.Path)';" data-id="@folder.Id">
                        <div class="card-footer">
                            <span class="file-manager__item-icon">
                                <i class="fas fa-folder"></i>
                            </span>
                            <h6 class="file-manager__item-title">@folder.Name</h6>
                        </div>
                    </div>
                </div>
            }
        }
    -->
</div>