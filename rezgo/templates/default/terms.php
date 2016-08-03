<div class="container-fluid">

  <div class="jumbotron">
    <h2 id="rezgo-terms-head">Booking Terms</h2>
    
    <div class="row">
    	<div class="rezgo-page-content">
			<?=$site->getPageContent('terms')?>
      <?php
			 if ($company->tripadvisor_url != '') {
				echo '<p class="rezgo-ta-privacy">Privacy Addendum <br />
				We may use third-party service providers such as TripAdvisor to process your personal information on our behalf. For example, we may share some information about you with these third parties so that they can contact you directly by email (for example: to obtain post visit reviews about your experience).</p>';
			 }
			?>
      </div>
    </div>
    
  </div>

</div>	

