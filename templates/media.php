<div id="examplePopup1" style="display:none">
    <h1>Image4IO Images</h1>
    <div class="image4io-folder-path">
        
    </div>
    <div class="image4io-images-container">
    </div>
</div>




























<!--<div class="main-content-container container">
    <div class="page-header row no-gutters py-4">
        <div class="col">
            <span class="text-uppercase page-subtitle">@GlobalSharedLocalizer["STORAGE"]</span>
            <h3 class="page-title">@Localizer["File Manager"]</h3>

        </div>
        <div class="col d-flex">
            <div class="d-inline-flex ml-auto my-auto">
                <span class="text-uppercase page-subtitle">
                    <a href="@Context.Request.Path">
                        @Localizer["HOME FOLDER"]
                    </a>
                    @{string currentLink = "";}
                    @foreach (var currentPath in Context.Request.Query["path"].ToString().Split('/'))
                    {
                        @if (!string.IsNullOrEmpty(currentPath))
                        {
                            currentLink += currentPath + "/";
                            <i class="fa fa-angle-right"></i><a href="@(Context.Request.Path + "?path=" + currentLink)">@currentPath</a>
                        }
                    }
                </span>
            </div>
        </div>
    </div>
    <div class="file-manager file-manager-cards">
        @await Html.PartialAsync("_Dropzone.cshtml", new Image4io.Console.Models.Storage.FileUploadModel(Context.Request.Query["path"]))
        <div class="row">
            <div class="col">
                <span class="file-manager__group-title text-uppercase text-light">@Localizer["FUNCTIONS"]</span>
            </div>
        </div>
        <div class="card card-small mb-3">
            <div class="row no-gutters p-2">
                <div class="col">
                    <div class="d-flex ml-lg-auto my-auto">
                        <div class="btn-group btn-group-sm mr-2 mr-lg-auto" role="group" aria-label="Table row actions">
                            <button class="btn btn-sm btn-danger d-inline-block ml-auto ml-lg-0" data-toggle="modal" data-target="#purgeModal">
                                <i class="far fa-trash-alt"></i> @Localizer["Create Purge Request"]
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <span class="file-manager__group-title text-uppercase text-light">@Localizer["FOLDERS"]</span>
            </div>
        </div>

        <div class="card card-small mb-3">
            <div class="row no-gutters p-2">
                <div class="col-lg-3 mb-2 mb-lg-0">
                </div>
                <div class="col">
                    <div class="d-flex ml-lg-auto my-auto">
                        <div class="btn-group btn-group-sm mr-2 ml-lg-auto" role="group" aria-label="Table row actions">
                            @if (Model.Folders.Count > 0)
                            {
                                <button type="button" class="btn btn-white" onclick="DeleteFolders()">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            }
                        </div>
                        <button class="btn btn-sm btn-accent d-inline-block ml-auto ml-lg-0" data-toggle="modal" data-target="#addNewFolderModal">
                            <i class="fas fa-plus"></i> @Localizer["Add New"]
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @if (Model.Folders.Count == 0)
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
        </div>
        <div class="row">
            <div class="col">
                <span class="file-manager__group-title text-uppercase text-light">@Localizer["IMAGES"]</span>
            </div>
        </div>
        @if (Model.Images.Count > 0)
        {
            <div class="card card-small mb-3">
                <div class="row no-gutters p-2">
                    <div class="col-lg-3 mb-2 mb-lg-0">
                    </div>
                    <div class="col">
                        <div class="d-flex ml-lg-auto my-auto">
                            <div class="btn-group btn-group-sm mr-2 ml-lg-auto" role="group" aria-label="Table row actions">
                                <button type="button" class="btn btn-white" onclick="DeleteImages()">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        }
        <div class="row" id="imagesRow">
            @if (Model.Images.Count == 0)
            {
                <div class="col-lg-3" id="noImage">
                    <div class="file-manager__item--directory card card-small mb-3">
                        <div class="card-footer">
                            <h6 class="file-manager__item-title">@Localizer["There is no image"]</h6>
                        </div>
                    </div>
                </div>
            }
            else
            {
                foreach (var image in Model.Images)
                {
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="file-manager__item file-manager__item--image card card-small mb-3" data-id="@image.Id">
                            <div class="file-manager__item-preview card-body px-0 pb-0 pt-4">
                                <img src="@image.Url" alt="File Manager - Item Preview">
                            </div>
                            <div class="card-footer border-top">
                                <span class="file-manager__item-icon">
                                    <i class="far fa-image"></i>
                                </span>
                                <h6 class="file-manager__item-title">@image.Name</h6>
                                <span class="file-manager__item-size ml-auto">@image.SizeText</span>
                            </div>
                            <div class="card-footer-info">
                                <span class="file-manager__item-icon">
                                    <i class="far fa-folder"></i>
                                </span>
                                <span class="file-manager__item-size">@image.Path</span>
                            </div>
                        </div>
                    </div>
                }
            }
        </div>
    </div>
</div>-->