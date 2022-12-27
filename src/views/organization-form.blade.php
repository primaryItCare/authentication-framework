
@php

$orgFiled = [];
if(!empty($organization)){
    $orgFiled = json_decode($organization->form_filed);
}
@endphp
<div class="row">  
    <input type="hidden" id="org_id" name="org_id" value="{{!empty($organization)?$organization->id:''}}">
    <input type="hidden" id="org_state" value="{{ (!empty($orgFiled) && ($orgFiled->state))?$orgFiled->state : ''}}">
    @foreach($formData as $key=>$item)  
        @if($item['is_display']) 
            <div class="col-sm-12 col-md-4">
                <div class="form-group">
                    @if($item['filed_type'] != 'select') 
                        <input type="{{$item['filed_type']}}" class="form-control" placeholder="{{$item['label_name']}}" name="{{$key}}" id="{{$key}}" {{$item['is_required']}} value="{{ (!empty($orgFiled) && ($orgFiled->$key))?$orgFiled->$key : ''}}" {{$isView}}>
                    @else
                        @if($key == 'country')
                            <select  class="custom-select w-100" name="{{$key}}" id="{{$key}}" {{$isView}} >
                                <option value="ss">Select</option>
                                @foreach($country as $ikey=>$item) 
                                    <option value="{{$ikey}}"{{(!empty($orgFiled)&&($orgFiled->$key == $ikey))?'selected':'' }}>{{$item}}</option>
                                @endforeach
                            </select>
                        @endif
                        @if($key == 'state')
                            <select  class="custom-select w-100" name="{{$key}}" id="{{$key}}" {{$isView}} >
                                <option value="">Select</option>
                            </select>
                        @endif
                    @endif
                </div>
            </div> 
        @endif 
    @endforeach
</div>