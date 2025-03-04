<!DOCTYPE html>
<html>
<head>
<title>Application Form</title>
<link rel="stylesheet" type="text/css" href="edit.css">
</head>
<body>
  <div class="container">  <!-- Added a container -->
    <form>
      <h1>Application Details</h1>

      <div class="form-group">
        <label for="fullname">Full name <span class="required">*</span></label>
        <div class="name-fields">  <!-- Flexbox wrapper for name inputs -->
          <input type="text" id="fullname" name="fullname" placeholder="First name" required>
          <input type="text" id="middlename" name="middlename" placeholder="Middle name">
          <input type="text" id="lastname" name="lastname" placeholder="Last name" required>
        </div>
      </div>
      <br>

      <div class="form-group">
        <label for="email">Email Address <span class="required">*</span></label>
        <input type="email" id="email" name="email" placeholder="Email Address" required>
      </div>
      <br>

      <div class="form-group">
        <label for="phone">Phone Number <span class="required">*</span></label>
        <input type="tel" id="phone" name="phone" placeholder="+(63)" required>
      </div>
      <br>

      <div class="form-group">
        <label for="position">Position Desire <span class="required">*</span></label>
        <input type="text" id="position" name="position" placeholder="Exp. Hairdresser" required>
      </div>
      <br>

      <div class="form-group">
        <label>Type of work <span class="required">*</span></label>
        <input type="radio" id="parttime" name="worktype" value="part-time">
        <label for="parttime">Part-time</label>
        <input type="radio" id="fulltime" name="worktype" value="full-time" checked>
        <label for="fulltime">Full time (Preferred)</label>
      </div>
      <br>

      <h2>Verification</h2>
      <div class="form-group">
        <label for="adminpassword">Admin Verification <span class="required">*</span></label>
        <input type="password" id="adminpassword" name="adminpassword" placeholder="Admin password" required>
      </div>

      <button type="submit">SUBMIT</button>
    </form>
  </div>
</body>
</html>