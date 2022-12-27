@extends('layouts.app')
@section('title','Organization List')
@section('content')   
<div class="survey-name-wrap">
    <div class="survey-name-block"> 
        <div class="row img-search-wrapper"> 
            <div class="col"> 
                <div class="new-survey-wrap">
                    <button type="button" class="btn btn-primary btn-sm create-group-btn" id="addOrganization" title="Create New Organization">Create New Organization</button>
                </div>
            </div>
        </div>
        <div class="col">
                <div class="table-block">
                    <table class="table" id="organizationTable">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                @foreach($formData as $key=>$item)
                                    @if($item['is_listing'])
                                        <th scope="col">{{$item['label_name']}}</th>
                                    @endif
                                @endforeach 
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody> 
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>  
    @include('organization::organization-modal') 
</div>
@endsection
@push('scripts') 
    <script type="text/javascript">
        $(document).ready(function(){
            $('#preloader').hide(); 
            getAjaxList(); 
            $('#addOrganization').click(function() {
                $.ajax({
                    url: appURL+'/organization/create', 
                    beforeSend : function(){ 
                        /*$('#preloader').show(); */
                    },
                    success: function(html){ 
                        $('#organizationTitle').html('Create New'); 
                        $('#create-new-organization').modal('show'); 
                        $('#preloader').hide();
                        $('#organizationFormWrap').html(html); 
                        $('#submit_btn').attr('type','submit');
                        $('#submit_btn,.right-block').removeClass('d-none');
                    }
                });
            });
            
        });
        $(document).delegate('.organizationView','click',function(){
            var id = $(this).attr('data-id'); 
            $.ajax({
                url: appURL+'/organization/view/'+id, 
                beforeSend : function(){ 
                    $('#preloader').show(); 
                },
                success: function(html){ 
                    $('#organizationTitle').html('View'); 
                    $('#create-new-organization').modal({
                        backdrop: 'static',
                        keyboard: false
                    }); 
                    $('#preloader').hide();
                    $('#organizationFormWrap').html(html);
                    $('#submit_btn,.right-block,#recaptchaBlock').addClass('d-none');
                    getState(); 
                }
            }); 
        }); 
        $(document).delegate('.organizationDelete','click',function(){ 
            var id = $(this).attr('data-id');
            swal({
                title: "Are you sure?",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, delete now",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            },
            function(e){
                if(e){
                    $.ajax({
                        url: appURL+'/organization/destroy',
                        data:{
                            '_token':getCSRFToken(), 
                            'id':id
                        },
                        type:'Delete',
                        dataType:'JSON',
                        beforeSend : function(){ 
                            $('#preloader').show(); 
                        },
                        success: function(responce){
                            swal.close(); 
                            $('#preloader').hide(); 
                            if(responce.success){
                                getAjaxList();
                                Lobibox.notify('success', {
                                    showClass: 'zoomInUp',
                                    hideClass: 'zoomOutDown',
                                    msg: responce.message
                                });
                            }else{
                                Lobibox.notify('error', {
                                    showClass: 'zoomInUp',
                                    hideClass: 'zoomOutDown',
                                    msg: responce.message
                                });
                            }
                        }
                    });
                }
            });
        }); 
        $(document).delegate('.organizationEdit','click',function(){
            var id = $(this).attr('data-id'); 
            $.ajax({
                url: appURL+'/organization/edit/'+id, 
                beforeSend : function(){ 
                    $('#preloader').show(); 
                },
                success: function(html){ 
                    $('#organizationTitle').html('Edit'); 
                    $('#create-new-organization').modal({
                        backdrop: 'static',
                        keyboard: false
                    }); 
                    $('#preloader').hide();
                    $('#organizationFormWrap').html(html);
                    $('#submit_btn,.right-block,#recaptchaBlock').removeClass('d-none');
                    $('#submit_btn').attr('type','submit'); 
                    getState();
                }
            }); 
        });
        $(document).delegate('#country','change',function(){
            var id = $('#country').val(); 
           getState(); 
        });
        function getState(){
            $.ajax({
                url: appURL+'/get-state-by-country',
                dataType:'JSON',
                data:{
                    'country_id':$('#country').val(),
                },
                beforeSend : function(){ 
                    $('#preloader').show(); 
                },
                success: function(responce){
                    $('#preloader').hide();
                    var stateId = $("#org_state").val(); 
                    var html  = '<option value="">Select</option>';
                    $.each(responce.data.state, function( index, val ) {
                        var selected = '';
                        if(stateId == index){
                            selected = 'selected';
                        }
                        html  += '<option value="'+index+'" '+selected+'>'+val+'</option>';
                    });
                    $("#state").html('').append(html);
                }
            }); 
        }
        $("#organizationForm").validate({
            rules: { 
                name: {
                    required: true,
                },  
            },
            messages: { 
                name: {
                    required: "Please enter a organization name.",
                },
            },    
            errorClass: "error",
            errorElement: "error",
            errorPlacement: function(error, element) {
                var placement = $(element).data('error');
                if (placement) {
                    $(element).next().append(error)
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler:function(form){ 
                $.ajax({
                    url :appURL+'/organization/add-in-db',
                    method : 'POST', 
                    data : new FormData($('#organizationForm')[0]),
                    dataType:'JSON',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend : function(){
                        $('#preloader').show();
                        $('#submit_btn').attr('type','button');
                    },
                    success :function(responce){ 
                        $('#preloader').hide();
                        if(responce.success){
                            Lobibox.notify('success', {
                                showClass: 'zoomInUp',
                                hideClass: 'zoomOutDown',
                                msg: responce.message
                            });
                            getAjaxList();
                            closeModel('create-new-organization');
                        }else{
                            Lobibox.notify('error', {
                                showClass: 'zoomInUp',
                                hideClass: 'zoomOutDown',
                                msg: responce.message
                            });
                            $('#submit_btn').attr('type','submit');
                        }
                    }
                });
            }
        }); 
        function getAjaxList(){
            var table = $('#organizationTable');
            var html = '[{"COLUMNS":[';
            $('th').each(function () {   
                html += '{ "data": "'+$(this).text().replace(" ", "")+'"},';
            });  
            html += ']}]';
            var columns = [];
            var dataObject = eval(html); 
            $('#organizationTable').DataTable({
                processing: false,
                serverSide: true,
                destroy: true,
                pageLength: 20,
                searching: true,
                autoWidth: true,
                aaSorting: [],
                columns: dataObject[0].COLUMNS,
                columnDefs: [{
                    orderable: false,
                    targets: [0, 2,3]
                }],
                lengthMenu: [
                    [10, 20, 50, -1],
                    [10, 20, 50, 'All'],
                ],
                language: {
                    paginate: {
                        next: 'Next &rarr;',
                        previous: '&larr; Prev'
                    }
                },
                ajax: {
                    url :appURL+'/organization/list',   
                    beforeSend: function() {
                        return true;
                    }
                },
            });
        }
        function closeModel(id){
            $("#"+id).modal('hide');
        }
    </script>
@endpush
