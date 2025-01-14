@extends('Shared.Layouts.Master')

@section('title')
@parent
@lang("Attendee.event_attendees")
@stop


@section('page_title')
<i class="ico-users"></i>
{{$event->title}}
@lang("ManageEvent.check-in-log")
@lang("instruction.instruction13")
@stop

@section('top_nav')
@include('ManageEvent.Partials.TopNav')
@stop

@section('menu')
@include('ManageEvent.Partials.Sidebar')
@stop

@section('head')
    <link rel="stylesheet" href="{{asset('assets/stylesheet/morris.css')}}" />
	<script src="{{asset('assets/javascript/raphael-min.js')}}"></script>
	<script src="{{asset('assets/javascript/morris.min.js')}}"></script>
	<link rel="stylesheet" href="{{asset('assets/stylesheet/bootstrap-datepicker.min.css')}}" />
	<script src="{{asset('assets/javascript/bootstrap-datepicker.min.js')}}"></script>
	<script src="{{asset('assets/javascript/tinymce/tinymce.min.js')}}"></script>
	<!-- jquery form builder -->
    <link rel="stylesheet" href="{{asset('vendor/bootstrap-form-builder/css/jquery.ui.theme.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/stylesheet/template.css')}}" />
	<script src="{{asset('vendor/bootstrap-form-builder/js/jquery.ui.min.js')}}"></script>
	<script src="{{asset('vendor/bootstrap-form-builder/js/beautifyhtml.js')}}"></script>

    {!! HTML::script(config('attendize.cdn_url_static_assets').'/dist/js/bootstrap-multiselect.js') !!}
	{!! HTML::style(config('attendize.cdn_url_static_assets').'/dist/css/bootstrap-multiselect.css') !!}
	<style>

		.dropdown-menu {
			border-radius: 0;
		}
		.form-control{
			border:none;
		}
		.multiselect-native-select {
			position: relative;
			select {
				border: 0 !important;
				clip: rect(0 0 0 0) !important;
				height: 1px !important;
				margin: -1px -1px -1px -3px !important;
				overflow: hidden !important;
				padding: 0 !important;
				position: absolute !important;
				width: 1px !important;
				left: 50%;
				top: 30px;
			}
		}
		.multiselect-container {
			position: absolute;
			list-style-type: none;
			margin: 0;
			padding: 0;
			.input-group {
				margin: 5px;
			}
			li {
				padding: 0;
				.multiselect-all {
					label {
						font-weight: 700;
					}
				}
				a {
					padding: 0;
					label {
						margin: 0;
						height: 100%;
						cursor: pointer;
						font-weight: 400;
						padding: 3px 20px 3px 40px;
						input[type=checkbox] {
							margin-bottom: 5px;
						}
					}
					label.radio {
						margin: 0;
					}
					label.checkbox {
						margin: 0;
					}
				}
			}
			li.multiselect-group {
				label {
					margin: 0;
					padding: 3px 20px 3px 20px;
					height: 100%;
					font-weight: 700;
				}
			}
			li.multiselect-group-clickable {
				label {
					cursor: pointer;
				}
			}
		}
		.btn-group {
			.btn-group {
					.multiselect.btn {
						border-top-left-radius: 4px;
						border-bottom-left-radius: 4px;
					}
			}
		}
		.form-inline {
			.multiselect-container {
				label.checkbox {
					padding: 3px 20px 3px 40px;
				}
				label.radio {
					padding: 3px 20px 3px 40px;
				}
				li {
					a {
						label.checkbox {
							input[type=checkbox] {
								margin-left: -20px;
								margin-right: 0;
							}
						}
						label.radio {
							input[type=radio] {
								margin-left: -20px;
								margin-right: 0;
							}
						}
					}
				}
			}
		}
		.tools a{
			display:none;
		}
	</style>
	<style>
        svg {
            width: 100% !important;
        }
    </style>
	<style>
        .page-header {
            display: none;
        }
	</style>
	<style>
		.droppable-active { background-color: #ffe !important; }
		.tools a { cursor: pointer; font-size: 80%; }
		.form-body .col-md-6, .form-body .col-md-12 { min-height: 400px; }
		.draggable { cursor: move; }
		.template-page{ 			
			background-repeat: no-repeat;
			background-size: 100% 100%;
    		background-position: center;
		}
		.template-page .header .logo-preview.left_position{
			float:left !important;
		}
		.template-page .header .logo-preview.right_position{
			float:right !important;
		}
		.template-page .header .logo-preview.center_position{
			float:none !important;
			margin: 0 auto;
		}
	</style>
	<script>
		function applyBorderSetting(){
			var top = $("#c_border_top").val();
			var bottom = $("#c_border_bottom").val();
			var left = $("#c_border_left").val();
			var right = $("#c_border_right").val();
			$('.template-page').css('padding-top', top + 'px');
			$('.template-page').css('padding-bottom', bottom + 'px');
			$('.template-page').css('padding-left', left + 'px');
			$('.template-page').css('padding-right', right + 'px');
		}
		function readBorderFile(input, id) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();				
				
				reader.onload = function (e) {
					$('.template-page').css('background-image', 'url(' + e.target.result + ')');
					/*
					var form_data = new FormData();
					form_data.append('file', input.files[0]);
					form_data.append('attendee_id', id);
					$.ajax({
							headers: {
								'X-CSRF-TOKEN': '{{ csrf_token() }}'
							},
							url         : '{{ url('/upload_photo') }}',
							dataType    : 'text',           // what to expect back from the PHP script, if anything
							cache       : false,
							contentType : false,
							processData : false,
							data        : form_data,                         
							type        : 'post',
							success     : function(output){

							},
							error: function(){

							}
					 });
					*/					 
				}
				reader.readAsDataURL(input.files[0]);				
			}
		}
		function deleteHtml(id) {
			if (confirm("Are you really delete this html?")) {

				$('#pdf-tr-'+id).css('display', 'none');
				var form_data = new FormData();
				form_data.append('sign_pdf_id', id);
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': '{{ csrf_token() }}'
					},
					url         : '{{ url('/delete_html_cert') }}',
					dataType    : 'text',           // what to expect back from the PHP script, if anything
					cache       : false,
					contentType : false,
					processData : false,
					data        : form_data,
					type        : 'post',
					success     : function(output){
						document.location.href="{{route('showOrganiserSignEvents', array('organiser_id' => $organiser->id))}}?sign_tab=4";
					}
				});
			}else{

			}
		}
        @include('ManageOrganiser.Partials.OrganiserCreateAndEditJS')
    </script>
@stop

@section('page_header')

@stop

@section('content')
	@if(isset($template))
		<h1>Certificate</h1>
	@else
		<h1>Empty Certificate</h1>
	@endif

    <div class="row">
		<div class="col-md-12">			
			<div class="col-md-12 col-sm-12 template-col">
				<form class="template-page edit-mode" template-id="<?=(isset($template))? $template->id:'0' ?>" style='background-image:<?=(isset($template))? $template->background: ""?>; <?=(isset($template) && isset($template->padding))? "padding:".$template->padding: ""?>'>
					<div class="header" element-type="header">
						<div><span class="title" value=""><?=(isset($template))? $template->title: "[TEMPLATE TITLE]" ?></span></div>
						@if(isset($template->logo) && $template->logo!="")
							<img src="<?=$template->logo; ?>" class="logo-preview <?=$template->logo_position; ?>" value="" alt="LOGO"/>						    
						@else
							<img src="{{asset('assets/images/apple-touch-icon.png')}}" class="logo-preview" value="" alt="LOGO"/>	
						@endif						
					</div>
					<div class="text-muted empty-form text-center" id="point_value" style="font-size: 24px;"></div>
					<div class="row form-body template-contents">
					@if(isset($template))
						<?=$template->contents?>
					@else
						<div class="col-md-12 droppable sortable">
						</div>
						<div class="col-md-6 droppable sortable" style="display: none;">
						</div>
						<div class="col-md-6 droppable sortable" style="display: none;">
						</div>
					@endif
					</div>
				</form>
			</div>
		</div>
    </div>
	
	<!-- Rich text edit-->
	<div  id="rich-edit-editor" role="dialog"  class="modal fade" style="display: none;">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header text-center">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h3 class="modal-title">
						<i class="ico-calendar"></i>
						@lang("basic.html_title")</h3>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group custom-theme">
								<textarea id="rich-text-content" name="content" class="textarea_big"></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-primary btn-success" >Set</button>
					<button class="btn btn-primary btn-cancel" >Cancel</button>
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
	
<script>
	var me=null;

	$("#logo_position input:radio").click(function() {

		if($(this).val()==3){
			$('.template-page.edit-mode .header .logo-preview').removeClass("center_position");
			$('.template-page.edit-mode .header .logo-preview').removeClass("right_position");
			$('.template-page.edit-mode .header .logo-preview').addClass("left_position");
		}else if($(this).val()==2){
			$('.template-page.edit-mode .header .logo-preview').removeClass("left_position");
			$('.template-page.edit-mode .header .logo-preview').removeClass("right_position");
			$('.template-page.edit-mode .header .logo-preview').addClass("center_position");
		}else{
			$('.template-page.edit-mode .header .logo-preview').removeClass("center_position");
			$('.template-page.edit-mode .header .logo-preview').removeClass("left_position");
			$('.template-page.edit-mode .header .logo-preview').addClass("right_position");
		}

	 });
	$(document).ready(function() {
		$("#org_name").html("{{$attendee->full_name}}");
		var ceu = {{$event->ceu_total}};
		var ceu_hr = 1*{{$event->ceu_hr}};
		var checkintime = {{gmdate("H", $attendee->period_in)}};
		var checkintime_m = {{gmdate("i", $attendee->period_in)}};
		console.log(checkintime_m);
		console.log(checkintime);
		console.log(ceu_hr/ceu);
		var ceu_val = 1.0*(1.0*ceu_hr/ceu)*checkintime+(1.0*ceu_hr/ceu)*checkintime_m/60; 
		console.log(checkintime/3600);
		console.log(ceu_val);
		$("#org_hrs").html(ceu_val.toFixed(2)+" {{$event->ceu_unit}}");
		setup_draggable();
	
		$("#n-columns").on("change", function() {
			var v = $(this).val();
			if(v==="1") {
				var $col = $('.form-body .col-md-12').toggle(true);
				$('.form-body .col-md-6 .draggable').each(function(i, el) {
					$(this).remove().appendTo($col);
				})
				$('.form-body .col-md-6').toggle(false);
			} else {
				var $col = $('.form-body .col-md-6').toggle(true);
				$(".form-body .col-md-12 .draggable").each(function(i, el) {
					$(this).remove().appendTo(i % 2 ? $col[1] : $col[0]);
				});
				$('.form-body .col-md-12').toggle(false);
			}
		});
	 
		$("#copy-to-clipboard").on("click", function() {
			var $copy = $(".form-body").parent().clone().appendTo(document.body);
			$copy.find(".tools, :hidden").remove();
			$.each(["draggable", "droppable", "sortable", "dropped",
				"ui-sortable", "ui-draggable", "ui-droppable", "form-body"], function(i, c) {
				$copy.find("." + c).removeClass(c);
			})
			var html = html_beautify($copy.html());
			$copy.remove();
	
			$modal = get_modal(html).modal("show");
			$modal.find(".btn").remove();
			$modal.find(".modal-title").html("Copy HTML");
			$modal.find(":input:first").select().focus();
			
			return false;
		})
		
		$('.template-page .header').click(function(){
			var header_el=$(this);
			var title=$(header_el).find('.title').text();
			var logo=$(header_el).find('.logo-preview').attr('src');

			var dlg_body=
				'<div class="row">\
					<div class="form-group">\
						<label class="col-md-4 col-form-label" for="title">Title</label>\
						<div class="col-md-8">\
							<input aria-describedby="textinputHelpBlock" id="new_title" type="text" value="'+title+'" class="form-control input-md">\
						</div>\
					</div>\
					<div class="form-group row draggable">\
						<label class="col-md-4 col-form-label" for="logo">Logo</label>\
						<div class="col-md-8">\
							<input aria-describedby="textinputHelpBlock" id="new_logo" type="file" value="" class="form-control input-md">\
						</div>\
					</div>\
				</div>';

			var $modal = get_modal('Edit Title',dlg_body).modal("show");
	
			$modal.find(".btn-success").click(function(ev2) {
				var new_title = $modal.find("#new_title").val();
				if(new_title==""){
					alert("Please input title of your template.");
					$modal.find("#new_title").focus();
					return false;
				}
				var input_files=$modal.find("#new_logo");
				if (typeof input_files !== 'undefined' && input_files[0] && input_files[0].files[0]) {
					var new_logo_file=input_files[0].files[0];					
					var reader = new FileReader();
					reader.onloadend = function () {
						var b64 = reader.result;//.replace(/^data:.+;base64,/, '');					
						$('.template-page .header .logo-preview').attr('src', b64);
					};
					reader.readAsDataURL(new_logo_file);
				}
				$(header_el).find('.title').text(new_title);

				$modal.modal("hide").remove();
				return false;
			});

			$modal.find(".btn-cancel").click(function(ev2) {
				$modal.modal("hide").remove();
				return false;
			});
		});
	});
	
	var setup_draggable = function() {
		$( ".draggable" ).draggable({
			appendTo: "body",
			helper: "clone"
		});
		$( ".droppable" ).droppable({
			accept: ".draggable",
			helper: "clone",
			hoverClass: "droppable-active",
			drop: function( event, ui ) {
				$(".empty-form").remove();
				var $orig = $(ui.draggable)
				if(!$(ui.draggable).hasClass("dropped")) {
					var $el = $orig
						.clone()
						.addClass("dropped")
						.removeClass("draggable")
						.css({"position": "static", "left": null, "right": null})
						.appendTo(this);
						
					// update id
					var id = $orig.find(":input").attr("id");
					
					if(id) {
						id = id.split("-").slice(0,-1).join("-") + "-" 
							+ (parseInt(id.split("-").slice(-1)[0]) + 1)
					
						$orig.find(":input").attr("id", id);
						$orig.find("label").attr("for", id);
					}
	
					// tools
					$('<p class="tools">\
						<a class="edit-link">Edit HTML<a> \
						<a class="remove-link">Remove</a></p>').appendTo($el);
				} else {
					if($(this)[0]!=$orig.parent()[0]) {
						var $el = $orig
							.clone()
							.css({"position": "static", "left": null, "right": null})
							.appendTo(this);
						$orig.remove();
					}
				}
			}
		}).sortable();
		
	}
	
	var get_modal = function(caption, dlg_body) {
		var modal = $('<div class="modal" style="overflow: auto;" tabindex="-1">\
			<div class="modal-dialog">\
				<div class="modal-content">\
					<div class="modal-header">\
						<a type="button" class="close"\
							data-dismiss="modal" aria-hidden="true">&times;</a>\
						<h4 class="modal-title">'+caption+'</h4>\
					</div>\
					<div class="modal-body ui-front">\
						<div class="row">'
						+dlg_body+
						'</div>\
						<button class="btn btn-success">Set</button>\
						<button class="btn btn-cancel">Cancel</button>\
					</div>\
				</div>\
			</div>\
			</div>').appendTo(document.body);
			
		return modal;
	};
	
	$('#rich-edit-editor').find(".btn-success").click(function(ev2) {
		var new_html = tinyMCE.activeEditor.getContent();
		me.find('.template-rich-text').html(new_html);

		$('#rich-edit-editor').modal("hide");
		return false;
	});

	$('#rich-edit-editor').find(".btn-cancel").click(function(ev2) {
		$('#rich-edit-editor').modal("hide");
		return false;
	});

	var editRichText=function($el,ev){
		me=$el;
		var old_html=$el.find('.template-rich-text').html();
		$('#rich-text-content').val(old_html);
		var html_editor=tinymce.init(html_editor_option);
		tinyMCE.activeEditor.setContent(old_html);
		var $modal = $('#rich-edit-editor').modal('show');
	};

	var editParagraph=function($el,ev){
		var old_para=$($el).find('.template-paragraph').html();
		
		var dlg_body=
					'<div class="form-group">\
						<textarea id="new-paragraph" class="form-control">'+old_para+'</textarea>\
					</div>';

		var $modal = get_modal('Edit Paragraph',dlg_body).modal("show");

		$modal.find(".btn-success").click(function(ev2) {
			var new_para = $modal.find("#new-paragraph").val();
			if(new_para==""){
				alert("Please input content of this paragraph. Will ignore change to new line.");
				$modal.find("#new-paragraph").focus();
				return false;
			}

			$($el).find('.template-paragraph').text(new_para);

			$modal.modal("hide").remove();
			return false;
		});

		$modal.find(".btn-cancel").click(function(ev2) {
			$modal.modal("hide").remove();
			return false;
		});
	};

	var editTextField=function($el,ev){
		var old_label=$($el).find('label').text();
		var old_val=$($el).find('input:text').val();
		var old_placeholder=$($el).find('input:text').attr('placeholder');
		
		var dlg_body=
					'<div class="form-group">\
						<div class="form-group row draggable" element-type="input-text">\
						<label class="col-md-4 col-form-label" for="label">Label</label>\
						<div class="col-md-8">\
							<input id="label" type="text" class="form-control input-md" value="'+old_label+'">\
						</div>\
					</div>\
					<div class="form-group">\
						<div class="form-group row draggable" element-type="input-text">\
						<label class="col-md-4 col-form-label" for="default">Default</label>\
						<div class="col-md-8">\
							<input id="default" type="text" class="form-control input-md" value="'+old_val+'">\
						</div>\
					</div>\
					<div class="form-group">\
						<div class="form-group row draggable" element-type="input-text">\
						<label class="col-md-4 col-form-label" for="placeholder">Placeholder</label>\
						<div class="col-md-8">\
							<input id="placeholder" type="text" class="form-control input-md" value="'+old_placeholder+'">\
						</div>\
					</div>';

		var $modal = get_modal('Edit Text Field',dlg_body).modal("show");

		$modal.find(".btn-success").click(function(ev2) {
			var new_label = $modal.find("#label").val();
			if(new_label==""){
				alert("Please input label for this text field.");
				$modal.find("#label").focus();
				return false;
			}

			$($el).find('label').text(new_label);
			$($el).find('input:text').val($($modal).find('#default').val());
			$($el).find('input:text').attr('placeholder',$($modal).find('#placeholder').val());

			$modal.modal("hide").remove();
			return false;
		});

		$modal.find(".btn-cancel").click(function(ev2) {
			$modal.modal("hide").remove();
			return false;
		});
	};

	var editTextArea=function($el,ev){
		var old_label=$($el).find('label').text();
		var old_val=$($el).find('textarea').val();
		
		var dlg_body=
					'<div class="form-group">\
						<div class="form-group row draggable" element-type="input-text">\
						<label class="col-md-4 col-form-label" for="label">Label</label>\
						<div class="col-md-8">\
							<input id="label" type="text" class="form-control input-md" value="'+old_label+'">\
						</div>\
					</div>\
					<div class="form-group">\
						<div class="form-group row draggable" element-type="input-text">\
						<label class="col-md-4 col-form-label" for="default">Default</label>\
						<div class="col-md-8">\
							<input id="default" type="text" class="form-control input-md" value="'+old_val+'">\
						</div>\
					</div>';

		var $modal = get_modal('Edit Text Field',dlg_body).modal("show");

		$modal.find(".btn-success").click(function(ev2) {
			var new_label = $modal.find("#label").val();
			if(new_label==""){
				alert("Please input label for this textarea.");
				$modal.find("#label").focus();
				return false;
			}

			$($el).find('label').text(new_label);
			$($el).find('textarea').val($($modal).find('#default').val());

			$modal.modal("hide").remove();
			return false;
		});

		$modal.find(".btn-cancel").click(function(ev2) {
			$modal.modal("hide").remove();
			return false;
		});
	};

	var editCheckGroup=function($el,ev){
		var old_label=$($el).children('label').text();
		var old_arr=[];
		var chks=$($el).find('input:checkbox');
		for(var i=0;i<chks.length;i++){
			old_arr.push($(chks[i]).val());
		}
		var old_val=old_arr.join('\n');

		var dlg_body=
					'<div class="form-group">\
						<div class="form-group row draggable" element-type="input-text">\
						<label class="col-md-4 col-form-label" for="label">Label</label>\
						<div class="col-md-8">\
							<input id="label" type="text" class="form-control input-md" value="'+old_label+'">\
						</div>\
					</div>\
					<div class="form-group">\
						<div class="form-group row draggable" element-type="input-text">\
						<label class="col-md-4 col-form-label" for="default">Default</label>\
						<div class="col-md-8">\
							<textarea id="default" class="form-control input-md">'+old_val+'</textarea>\
						</div>\
					</div>';

		var $modal = get_modal('Edit Text Field',dlg_body).modal("show");

		$modal.find(".btn-success").click(function(ev2) {
			var new_label = $modal.find("#label").val();
			if(new_label==""){
				alert("Please input label for this textarea.");
				$modal.find("#label").focus();
				return false;
			}

			var new_val=$($modal).find('#default').val();
			if(new_val==""){
				alert("Please option values of this checkbox group.");
				$($modal).find('#default').focus();
				return false;
			}

			$($el).find('label').text(new_label);
			var new_arr=new_val.split('\n');
			$el.find('div.col-md-8').empty();
			for(var i=0;i<new_arr.length;i++){
				ch_item=
						'<div class="checkbox">\
							<label for="checkboxes-0">\
								<input type="checkbox" id="checkboxes-0" value="'+new_arr[i]+'">'
								+new_arr[i]+
							'</label>\
						</div>';
				$el.find('div.col-md-8').append(ch_item);
			}

			$modal.modal("hide").remove();
			return false;
		});

		$modal.find(".btn-cancel").click(function(ev2) {
			$modal.modal("hide").remove();
			return false;
		});
	};

	var editSign=function($el,ev){
		var old_label1=$($el).find('label:first').text();
		var old_label2=$($el).find('label:last').text();
		
		var dlg_body=
					'<div class="form-group">\
						<label class="col-md-4 col-form-label" for="label">Label</label>\
						<div class="col-md-8">\
							<input id="label1" type="text" class="form-control input-md" value="'+old_label1+'">\
						</div>\
					</div>\
					<div class="form-group hidden">\
						<label class="col-md-4 col-form-label" for="label">Right Label</label>\
						<div class="col-md-8">\
							<input id="label2" type="text" class="form-control input-md" value="'+old_label2+'">\
						</div>\
					</div>';

		var $modal = get_modal('Edit Sign Components',dlg_body).modal("show");

		$modal.find(".btn-success").click(function(ev2) {
			var new_label1 = $modal.find("#label1").val();
			if(new_label1==""){
				alert("Please input label for this text field.");
				$modal.find("#label1").focus();
				return false;
			}
			$($el).find('label:first').text(new_label1);
			
			$modal.modal("hide").remove();
			return false;
		});

		$modal.find(".btn-cancel").click(function(ev2) {
			$modal.modal("hide").remove();
			return false;
		});
	};
	var editOrgSign=function($el,ev){
		var old_label1=$($el).find('label:first').text();
		
		var dlg_body=
					'<div class="form-group">\
						<label class="col-md-4 col-form-label" for="label">Label</label>\
						<div class="col-md-8">\
							<input id="label1" type="text" class="form-control input-md" value="'+old_label1+'">\
						</div>\
					</div>';

		var $modal = get_modal('Edit Organiser Sign Components',dlg_body).modal("show");

		$modal.find(".btn-success").click(function(ev2) {
			var new_label1 = $modal.find("#label1").val();
			if(new_label1==""){
				alert("Please input label for this text field.");
				$modal.find("#label1").focus();
				return false;
			}
			$($el).find('label:first').text(new_label1);
			
			$modal.modal("hide").remove();
			return false;
		});

		$modal.find(".btn-cancel").click(function(ev2) {
			$modal.modal("hide").remove();
			return false;
		});
	};
	var editOrgName=function($el,ev){
		var old_label1=$($el).find('label:first').text();
		
		var dlg_body=
					'<div class="form-group">\
						<label class="col-md-4 col-form-label" for="label">Label</label>\
						<div class="col-md-8">\
							<input id="label1" type="text" class="form-control input-md" value="'+old_label1+'">\
						</div>\
					</div>';

		var $modal = get_modal('Edit Organiser Name Components',dlg_body).modal("show");

		$modal.find(".btn-success").click(function(ev2) {
			var new_label1 = $modal.find("#label1").val();
			if(new_label1==""){
				alert("Please input label for this text field.");
				$modal.find("#label1").focus();
				return false;
			}
			$($el).find('label:first').text(new_label1);
			
			$modal.modal("hide").remove();
			return false;
		});

		$modal.find(".btn-cancel").click(function(ev2) {
			$modal.modal("hide").remove();
			return false;
		});
	};
	var editOrgDate=function($el,ev){
		var old_label1=$($el).find('label:first').text();
		
		var dlg_body=
					'<div class="form-group">\
						<label class="col-md-4 col-form-label" for="label">Label</label>\
						<div class="col-md-8">\
							<input id="label1" type="text" class="form-control input-md" value="'+old_label1+'">\
						</div>\
					</div>';

		var $modal = get_modal('Edit Organiser Name Components',dlg_body).modal("show");

		$modal.find(".btn-success").click(function(ev2) {
			var new_label1 = $modal.find("#label1").val();
			if(new_label1==""){
				alert("Please input label for this text field.");
				$modal.find("#label1").focus();
				return false;
			}
			$($el).find('label:first').text(new_label1);
			
			$modal.modal("hide").remove();
			return false;
		});

		$modal.find(".btn-cancel").click(function(ev2) {
			$modal.modal("hide").remove();
			return false;
		});
	};
	$(document).on("click", ".edit-link", function(ev) {
		var $el = $(this).parent().parent();
		var el_type=$($el).attr('element-type');

		switch(el_type){
		case 'richtext':
			editRichText($el,ev);
			break;
		case 'paragraph':
			editParagraph($el,ev);
			break;
		case 'input-text':
			editTextField($el,ev);
			break;
		case 'textarea':
			editTextArea($el,ev);
			break;
		case 'radio-group':

			break;
		case 'checkbox-group':
			editCheckGroup($el,ev);
			break;
		case 'sign':
			editSign($el,ev);
			break;
		case 'org_sign':
			editOrgSign($el,ev);
			break;
		case 'org_name':
			editOrgName($el,ev);
			break;
		case 'org_date':
			editOrgDate($el,ev);
			break;
		default:
			return false;
		}
/*
		var $el_copy = $el.clone();
		
		var $edit_btn = $el_copy.find(".edit-link").parent().remove();
	
		var $modal = get_modal(html_beautify($el_copy.html())).modal("show");
		$modal.find(":input:first").focus();
		$modal.find(".btn-success").click(function(ev2) {
			var html = $modal.find("textarea").val();
			if(!html) {
				$el.remove();
			} else {
				$el.html(html);
				$edit_btn.appendTo($el);
			}
			$modal.modal("hide");
			return false;
		});
*/
		
	});
	
	$('.bn-save-template').click(function(){
		var title=$('.template-page .header .title').text();
		var logo=$('.template-page .header .logo-preview').attr('src');
		var contents=$('.template-page .template-contents').html();
		var background=$(".template-page").css("background-image");
		var padding=$(".template-page").css("padding");
		var top = $("#c_border_top").val();
		var bottom = $("#c_border_bottom").val();
		var left = $("#c_border_left").val();
		var right = $("#c_border_right").val();
		var logo_position = $('input[name=logoposition]:checked').val()
		if(logo_position==3){
			logo_position_str = "left_position";
		}else if(logo_position==2){
			logo_position_str = "center_position";
		}else{
			logo_position_str = "right_position";
		}
		var data={
			'title':title,
			'logo':logo,
			'contents':contents,
			'background':background,
			'top':top,
			'bottom':bottom,
			'left':left,
			'right':right,
			'padding':padding,
			'logo_position':logo_position_str,
			'action':'<?=(isset($template))?"update":"create"?>',
			'template_id': $('.template-page').attr('template-id')
		};

		var ret=false;
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': '{{ csrf_token() }}'
			},
			url:"{{route('saveCertTemplate', array('organiser_id' => $organiser->id))}}",
			type:'post',
			data:data,
			success:function(data){
				if(data==='OK'){
					alert('Saved');
					location.href="{{route('showOrganiserSignEvents', array('organiser_id' => $organiser->id))}}?sign_tab=4#OrganiserCertTemplateForm";
				}
				else{
					alert('Cannot be saved.');
					ret=false;
				}
			},
			error:function(){
				alert('error');
				ret=false;
			}
		});
		return false;
	});

	$('.bn-discard-template').click(function(){
		return confirm('Do you want to cancel editing template?\nThis template will not be saved.');
	});

	var html_editor_option={
        selector: "#rich-text-content",

        fontsize_formats: "8px 10px 12px 14px 18px 24px 36px",
        plugins: [
                "advlist autolink autosave link image lists charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "table contextmenu directionality emoticons template textcolor paste textcolor colorpicker textpattern"
        ],

        toolbar1: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
        toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | insertdatetime preview | forecolor backcolor",
        toolbar3: "table | hr removeformat | subscript superscript | charmap | print fullscreen | ltr rtl | visualchars visualblocks nonbreaking template pagebreak restoredraft",

        menubar: false,
        toolbar_items_size: 'small',

        style_formats: [
                {title: 'Line spacing 10', inline: 'span', styles: {lineHeight:'10px'}},
                {title: 'Line spacing 12', inline: 'span', styles: {lineHeight:'12px'}},
                {title: 'Line spacing 14', inline: 'span', styles: {lineHeight:'14px'}},
                {title: 'Line spacing 16', inline: 'span', styles: {lineHeight:'16px'}},
                {title: 'Line spacing 18', inline: 'span', styles: {lineHeight:'18px'}},
                {title: 'Line spacing 20', inline: 'span', styles: {lineHeight:'20px'}},
                {title: 'Line spacing 22', inline: 'span', styles: {lineHeight:'22px'}},
                {title: 'Line spacing 24', inline: 'span', styles: {lineHeight:'24px'}},
                {title: 'Line spacing 26', inline: 'span', styles: {lineHeight:'26px'}},
                {title: 'Line spacing 28', inline: 'span', styles: {lineHeight:'28px'}},
                {title: 'Line spacing 30', inline: 'span', styles: {lineHeight:'30px'}},
                {title: 'Line spacing 32', inline: 'span', styles: {lineHeight:'32px'}},
                {title: 'Line spacing 34', inline: 'span', styles: {lineHeight:'34px'}},
                {title: 'Line spacing 36', inline: 'span', styles: {lineHeight:'36px'}},
                {title: 'Line spacing 38', inline: 'span', styles: {lineHeight:'38px'}},
                {title: 'Line spacing 40', inline: 'span', styles: {lineHeight:'40px'}},
                {title: 'Example 1', inline: 'span', classes: 'example1'},
                {title: 'Example 2', inline: 'span', classes: 'example2'},
                {title: 'Example 3', inline: 'span', classes: 'example3'},
                {title: 'Example 4', inline: 'span', classes: 'example4'},
                {title: 'Example 5', inline: 'span', classes: 'example5'}
        ],

        templates: [
                {title: 'Test template 1', content: 'Test 1'},
                {title: 'Test template 2', content: 'Test 2'}
        ],

        extended_valid_elements: 'script[src|async|defer|type|charset]',
        valid_children : '+body[style|link],+p[style|link],+div[style|link]'

	};
	

	$(document).on("click", ".remove-link", function(ev) {
		$(this).parent().parent().remove();
	});
	</script>
@stop
