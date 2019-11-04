
<script>
	function removeLoader() {
		var loader = window.top.document.getElementById('rezgo-modal-loader');
		loader.style.display = 'none';
	}
	window.onload = function(){
		removeLoader();
	}
</script>

<style media="print">
	#rezgo-waiver-wrp.rezgo-modal-wrp .tab-text .body {
		height: auto;
		overflow: visible;
	}
	#rezgo-waiver-wrp.rezgo-modal-wrp .tab-text .footer {
		display: none;
	}
	#scroll-down-info {
		display: none !important;
	}
</style>

<style>
	#scroll-down-info {
		padding: 6px 12px !important;
	}
	.pax_year { 
		padding-left:0 !important;
		padding-right:6px !important;
	}
	.pax_month { 
		padding-left:6px !important;
		padding-right:6px !important;
	}
	.pax_day { 
		padding-left:6px !important;
		padding-right:0 !important;
	}
	.rezgo-waiver-error {
		font-size: 90%;
		color: #a94442;
	}
	.rezgo-waiver-label-error {
		color: #a94442;
	}	
</style>

<div id="rezgo-waiver-wrp" class="container-fluid rezgo-container rezgo-modal-wrp">
	<div class="clearifx">
  
		<div id="tab-text" class="tab-text">
			<div class="body">
				<div class="row">
					<div class="col-md-12 rezgo-waiver-modal-text">

            <div id="scroll-down-info" class="alert alert-warning fade in" style="display:none;">
              <span>Please scroll down to sign waiver.</span>
            </div>

            <?php 
							// display waiver content from cart IDs
							echo $site->getWaiverContent($_REQUEST['ids']); 
						?>

						<div id='signature-area' style='display:none;'>
							<hr>

							<div class="row">
								<div class="col-xs-12">
									<small>Signature:</small>
								</div>
							</div>

							<div class="row">
								<div class="col-xs-12">
									<img id='signature-img' alt='signature' />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="footer">
        <div class="row">
          <div id="rezgo-sign-nav">
            <div class="col-xs-6">
              <button id="sign" class="btn rezgo-btn-default btn-block" disabled>
                <i class="fa fa-pencil bigger-110"></i>
                <span id="rezgo-sign-nav-txt"> Sign Waiver</span>
              </button>
            </div>

            <div class="col-xs-6">
              <button id="print" class="btn rezgo-btn-print btn-block">
                <i class="fa fa-print bigger-110"></i>
                <span> Print Waiver</span>
              </button>
            </div>
          </div>
				</div>
			</div>
		</div>

		<div class="tab-sign" style="display:none;">
			<div id="signature-pad">
				<div class="body">
					<p>Please sign in the space below</p>
					<canvas></canvas>
				</div>
				<div class="footer">
					<div class="row">
						<div class="col-xs-6">
							<button id="clear" class="btn rezgo-btn-default btn-block" data-action="clear" type="button">
								<i class="fa fa-times bigger-110"></i>
								<span> Clear</span>
							</button>
						</div>
						<div class="col-xs-6">
							<button id="save" class="btn rezgo-btn-book btn-block" data-action="save" type="button">
								<i class="fa fa-check bigger-110"></i>
								<span> Save</span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
    
	</div>
</div>
<script src="<?php echo $site->path;?>/js/signature_pad.min.js"></script>
<script src="<?php echo $site->path;?>/js/signature_pad_remove_blank.js"></script>
<script>
	var 
	signature = parent.rezgo_content_frame.document.getElementById('rezgo-waiver-input').value,
	
	receiver = window.top.document.getElementById('rezgo_content_frame').contentWindow,
	waiverModal = document.getElementById('rezgo-waiver-wrp'),
	signButton = document.getElementById('sign'),
	signBtnTxt = document.getElementById('rezgo-sign-nav-txt'),
	printButton = document.getElementById('print'),
	saveButton = document.getElementById('save'),
	signaturePad = document.getElementById('signature-pad'),
	clearButton = signaturePad.querySelector('[data-action=clear]'),
	undoButton = signaturePad.querySelector('[data-action=undo]'),
	canvas = signaturePad.querySelector('canvas'),
	waiverTxt = document.getElementById('tab-text'),
	waiverTxtBody = waiverTxt.getElementsByClassName('body')[0],
	waiverSignArea = document.getElementById('signature-area'),
	waiverSignImg = document.getElementById('signature-img'),
	scrollDownInfo = document.getElementById('scroll-down-info'),
	
	firstName = document.getElementById('pax_first_name'),
	lastName = document.getElementById('pax_last_name'),
	paxPhone = document.getElementById('pax_phone'),
	paxEmail = document.getElementById('pax_email'),
	
	paxSignature = document.getElementById('pax_signature'),
	signaturePad = new SignaturePad(canvas);

	function resizeCanvas() {
		var ratio =  Math.max(window.devicePixelRatio || 1, 1);
		canvas.width = canvas.offsetWidth * ratio;
		canvas.height = canvas.offsetHeight * ratio;
		canvas.getContext("2d").scale(ratio, ratio);
		signaturePad.clear();
	}
	
	function printWaiver(e) {
		setTimeout(function() { 
			window.focus(); 
			window.print(); 
		}, 200);
	}
	
	function showSignaturePad(e) {
		jQuery(".tab-text").hide();
		jQuery(".tab-sign").show();
		resizeCanvas();
	}
	
	function clearSignature(e) {
		signaturePad.clear();
	}
	
	function check_scroll(e) {
		var elem = e.target;
		
		if(elem.scrollTop >= (elem.scrollHeight - elem.offsetHeight)) {
			signButton.disabled = false;
		}
	}
	
	function checkOverflow(el) {
		var curOverflow = el.style.overflow;

		if(!curOverflow || curOverflow === "visible") el.style.overflow = "hidden";

		var isOverflowing = el.clientHeight < el.scrollHeight;

		el.style.overflow = curOverflow;

		return isOverflowing;
	}
	
	function saveSignatureOrder(e) {
		if (signaturePad.isEmpty()) {
			alert("Please provide a signature first.");
		} else {
			e.preventDefault();

			canvas.style.visibility = 'hidden';
			addSignature(signaturePad.toDataURL());

			var msg = {
				type:'modal',
				mode:'order_waiver',
				sig: signaturePad.toDataURL()
			};
			
			receiver.postMessage(msg, '*');
			
		}
	}
	
	function addSignature(req) {
		waiverSignArea.style.display = 'block';
		waiverSignImg.src = req;
		signBtnTxt.innerHTML = 're-sign waiver';
	}
	
	function back() {
		backButton.style.display = "none";
		jQuery(".tab-text").show();
		jQuery(".tab-sign").hide();
	}

	saveButton.addEventListener('click', saveSignatureOrder);
	
	signButton.addEventListener('click', showSignaturePad);
	printButton.addEventListener('click', printWaiver);
	clearButton.addEventListener('click', clearSignature);

	window.onresize = resizeCanvas;

	waiverTxtBody.addEventListener('scroll', check_scroll);

	if (!checkOverflow(waiverTxtBody)) {
		signButton.disabled = false;
	} else {
		scrollDownInfo.style.display = 'block';
	}
	
	if(signature !== '') {
		addSignature(signature);
	}
		
</script>
