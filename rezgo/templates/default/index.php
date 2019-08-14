<div id="rezgo-index-wrp" class="container-fluid rezgo-container">
  <div class="row">
	  <?php if ($site->getPageContent('intro')) { ?>
	    <div class="rezgo-intro col-xs-12">
	        <?php echo $site->getPageContent('intro')?>
	    </div>
	  <?php } ?>

	  <?php echo $site->getTemplate('topbar_order')?>

	  <div class="col-xs-12" id="rezgo-list-content"></div>

	  <div class="col-xs-12" id="rezgo-list-content-footer"></div>

	  <div class="col-xs-12" id="rezgo-list-content-more">
				<button type="button" class="btn btn-default btn-lg btn-block" id="rezgo-index-more-button" data-rezgo-page="<?php echo $site->requestNum('pg')?>">
					<span><i class="fa fa-list"></i>&nbsp;View more items &hellip;</span>
				</button>
	  </div>

	  <div class="col-xs-12" id="rezgo-list-content-bottom">&nbsp;</div>
  </div>
</div><!-- // .rezgo-container -->

<script>
  var start = 1;
  var search_start_date = '<?php echo $site->requestStr('start_date')?>';
  var search_end_date = '<?php echo $site->requestStr('end_date')?>';
  var search_tags = '<?php echo $site->requestStr('tags')?>';
  var search_in = '<?php echo $site->requestStr('search_in')?>';
  var search_for = '<?php echo $site->requestStr('search_for')?>';
  var cid = '<?php echo $site->requestNum('cid')?>';

  $(document).ready(function() {
          
    $content = $('#rezgo-list-content');
    $footer = $('#rezgo-list-content-footer');
      
    $footer.html('<div class="rezgo-wait-div"></div>');
    
    $.ajax({
      url: '<?php echo $site->base;?>/index_ajax.php?pg=' + start + '&start_date=' + search_start_date + '&end_date=' + search_end_date + '&tags=' + search_tags + '&search_in=' + search_in + '&search_for=' + search_for + '&cid=' + cid,
      context: document.body,
      success: function(data) {				
        
        $footer.html('');
        
        var split = data.split('|||');
        
        $content.append(split[0]);
        
        $('#rezgo-ajax-container-' + start).fadeIn('slow', function() {
          
          if(split[1] == 1) {
            $('#rezgo-list-content-more').show();
            start++;	
            window.console.log('page ' + start);
          }
          
        });
    
        if ('parentIFrame' in window) {
            setTimeout(function(){ // fix FireFox timing issue
                parentIFrame.size();
            },0);								
            window.console.log('parentIFrame.size()');
        }	
        
      }
    });	
    
		$('#rezgo-index-more-button').click(function() {
      
      var page_num = $(this).attr('data-rezgo-page'); 
      //alert(page_num);
      
      $footer.html('<div class="rezgo-wait-div"></div>');
      $('#rezgo-list-content-more').fadeOut();
      //(Number(page_num) + 1)
      $.ajax({
        url: '/index_ajax.php?pg=' + start + '&start_date=' + search_start_date + '&end_date=' + search_end_date + '&tags=' + search_tags + '&search_in=' + search_in + '&search_for=' + search_for + '&cid=' + cid,
        context: document.body,
        success: function(data) {
          
          $footer.html('');
          
          var split = data.split('|||');
          
          $content.append(split[0]);
          
          $('#rezgo-ajax-container-' + start).fadeIn('slow', function() {
            
            if(split[1] == 1) {
              //$(this).attr('data-rezgo-page', (Number(page_num) + 1) );
              $('#rezgo-list-content-more').show();
              start++;	
              window.console.log('page ' + start);
            }
            
          });
    
          if ('parentIFrame' in window) {
              setTimeout(function(){ // fix FireFox timing issue
                  parentIFrame.size();
              },0);								
              window.console.log('parentIFrame.size()');
          }	
          
        }
        
      });	
      
      
    });			
    
    
  });

</script> 