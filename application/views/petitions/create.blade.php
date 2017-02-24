@layout('layouts/auth')

@section('content')
    <div clpass="row">
        <div class="col-sm-offset-1 col-md-offset-1 col-lg-offset-1 col-sm-10 col-md-10 col-lg-10">
            <div class="panel panel-default">
                <div class="panel-heading">Nieuwe petitie:</div>
                <div class="panel-body">
                    <form class="form-horizontal" action="{{ base_url('manifest/store') }}" method="post">
                        {{-- TODO: Implement CSRF token --}}

                        <div class="form-group">
                            <label for="title" class="control-label col-md-2">
                                Titel: <span class="text-danger">*</span>
                            </label>

                            <div class="col-md-5">
                                <input type="text" name="title" value=""class="form-control"  placeholder="Petitie titel">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="category" class="control-label col-sm-2">
                                Categorie: <span class="text-danger">*</span>
                            </label>

                            <div class="col-md-5">
                                <select class="form-control" name="category">
                                    <option value=""> -- selecteer categorie -- </option>

                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"> {{ $category->category_name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="letter" class="col-sm-2 control-label">
                                Manifest: <span class="text-danger">*</span>
                            </label>

                            <div class="col-md-8">
                                <textarea name="description" rows="7" class="form-control" placeholder="Het petitie manifest"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-offset-2 col-md-10">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <span class="fa fa-check" aria-hidden="true"></span> Aanmaken
                                </button>

                                <button type="reset" class="btn btn-sm btn-danger">
                                    <span class="fa fa-undo" aria-hidden="true"></span> Reset formulier
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
