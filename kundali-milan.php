<?php include '/header.php'; ?>
<div class="page-container">
<main class="main-content">
  <section class="service-form">
    <h1 class="form-title">कुंडली मिलान फॉर्म</h1>
    <form class="service-form" method="post" action="#">
      <fieldset>
        <legend>वराची माहिती (Groom Details)</legend>
        <div class="form-group">
          <label for="groom_name">वराचे नाव *</label>
          <input type="text" id="groom_name" name="groom_name" required>
        </div>
        <div class="form-group">
          <label for="groom_dob">जन्म तारीख *</label>
          <input type="date" id="groom_dob" name="groom_dob" required>
        </div>
        <div class="form-group">
          <label for="groom_tob">जन्म वेळ *</label>
          <input type="time" id="groom_tob" name="groom_tob" required>
        </div>
        <div class="form-group">
          <label for="groom_place">जन्म स्थान *</label>
          <input type="text" id="groom_place" name="groom_place" required>
        </div>
      </fieldset>
      <fieldset>
        <legend>वधूची माहिती (Bride Details)</legend>
        <div class="form-group">
          <label for="bride_name">वधूचे नाव *</label>
          <input type="text" id="bride_name" name="bride_name" required>
        </div>
        <div class="form-group">
          <label for="bride_dob">जन्म तारीख *</label>
          <input type="date" id="bride_dob" name="bride_dob" required>
        </div>
        <div class="form-group">
          <label for="bride_tob">जन्म वेळ *</label>
          <input type="time" id="bride_tob" name="bride_tob" required>
        </div>
        <div class="form-group">
          <label for="bride_place">जन्म स्थान *</label>
          <input type="text" id="bride_place" name="bride_place" required>
        </div>
      </fieldset>
      <fieldset>
        <legend>संपर्क (Contact)</legend>
        <div class="form-group">
          <label for="mobile">मोबाईल नंबर *</label>
          <input type="tel" id="mobile" name="mobile" required pattern="[0-9]{10,15}">
        </div>
        <div class="form-group">
          <label for="notes">अतिरिक्त माहिती (Optional)</label>
          <textarea id="notes" name="notes" rows="3"></textarea>
        </div>
      </fieldset>
      <button type="submit" class="submit-btn">Submit for Kundali Milan</button>
    </form>
  </section>
</main>
</div>
</style>

<?php include '/footer.php'; ?>
