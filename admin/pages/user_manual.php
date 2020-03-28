<div class="wrap ac-container">
	<h1 class="wp-heading-inline"><?php esc_attr_e( 'User Manual', self::DOMAIN ) ?></h1>
	<hr>

	<h1 class="atitle">1. After Installation</h1>
	<div class="acontent">
		<p>
			Thank you for installing our Plugin. After the installation, the first step would be to go to the plugin settings page and fill in the following details as needed.
		</p>
		<ul>
			<li><b>Job Form Page:</b> Choose the page from the drop-down menu where you’d like the candidates to see the complete details of the job they’ve chosen to apply at. The Job Application Form will also appear on this page so the candidates can apply for the job after reading the complete details. Don’t add any content to this page because any extra content you’d add to this page won’t appear on the front-end. Visitors on the front-end site will only see the Job Application Form, and the Job Ad details they have clicked at.</li>
			<li><b>Company name:</b> Enter the name of your company here. Use the code {company_name} in email templates, and the plugin will fetch the name of your company in your email message automatically.</li>
			<li><b>From name and email address:</b> The name and email address that you would like the applicants to see when they receive an email from you.</li>
			<li><b>Email Address:</b> If you’d like to receive the job applications via email, then please enter a valid email address in the email address text box on the settings page. You’ll receive an email on your registered email address from the system as soon as someone applies for a job. If you leave this box empty or enter an invalid email address but have attached an email template with the event "Email to admin on new job application" in email templates, then the system will start sending the job applications at the Administrator's email address. See option 5 for details regarding email templates.</li>
			<li><b>Deleting Job Applications Automatically:</b> Our plugin is GDPR compliant, and if you’d like the job applications to be automatically deleted, then please select the number of days from the drop-down menu on the settings page after which you’d like the application to be deleted. You can choose between 10-500 days. If you don't want the applications to be deleted, then choose the never option.</li>
			<li><b>Currency:</b> If you plan to mention salary or hourly rate on the job ad, then please select your default currency.</li>
			<li>
				<b>Setting up HR role:</b>
				A new role "<b>SA Job (HR)</b>" will be available for you when you are adding or modifying a user on your site. Users whom you have assigned the HR role will have access to all functionalities of our plugin, but they won't be able to access anything else on your site.
			</li>
		</ul>
	</div>

	<h1 class="atitle">2. Setting up Job Listings Page</h1>
	<div class="acontent">
		<p>
			We have two different page layouts/designs. You can use the one which suits your needs better. Paste one of the following shortcodes on any page where you want to advertise your jobs/show your job listings/openings.
		</p>
		<ul>
			<li>For design 1 use <code>[sa_jobs_list_design1]</code></li>
			<li>For design 2 use <code>[sa_jobs_list_design2]</code></li>
		</ul>
		<p>
			For example, you want to show your job ads on the home page, open all pages and click on the home page. When the home page opens, paste one of the above-mentioned shortcodes there and click update. You are done, wasn’t that easy? Your job ads will now start appearing on your home page.
		</p>
		<p>
			If you do not like the first layout, you may paste the other shortcode on the page where you want to advertise your jobs and click update. Now you are using the second design 😊
		</p>
	</div>

	<h1 class="atitle">3. Advertising Jobs on Multiple Pages</h1>
	<div class="acontent">
		<p>
			You can also advertise your jobs on multiple pages. Paste the shortcodes on multiple pages, and the job ads added by you that are active will start appearing on multiple pages.
		</p>
		<p>
			But to advertise jobs, you’d first need to add some job ads to the system. Follow the guideline below to learn how to add a new job opening.
		</p>
	</div>

	<h1 class="atitle">4. Adding/Posting Job Ads</h1>
	<div class="acontent">
		<p>
			Advertising job openings was never this easy. You have the power to post unlimited job ads, use the instructions below to post a new job ad.
		</p>
		<ul>
			<li>Go to job ads page and click Add New Job or Press Ctrl + M on your keyboard</li>
			<li>A new page will appear, fill out the necessary and optional information and press save
				<p>
				<b>Necessary info:</b><br>
				Job Title, Job Description, Job Category, and Expiration Date
				</p>
				<p>
					<b>Optional info:</b><br>
					<u><i>CC & BCC email address:</i></u> use these functions only if you want your colleague, client, or anyone else to receive an email when someone applies for this job, a copy of the email that is sent to the admin will be sent at these mail addresses<br>
					<u><i>Job location:</i></u> write the location of the job here<br>
					<u><i>External link:</i></u> if you want to redirect the applicant to a different page upon clicking the apply button, then add that URL over here. This is very helpful for recruitment agencies, as they want the applicants to not apply for the job on their website but at a different site. If no URL is added here, then the applicant will see the normal job form.<br>
					<u><i>Job type:</i></u> select the job type from the drop-down menu if you’d like.<br>
					<u><i>Salary and salary type:</i></u> enter the salary amount, and the type of salary (only if you wish to mention it on the front-end, and want your applicants to see it).<br>
					<u><i>Currency:</i></u> you can show the salary in different currencies, one job is in Asia, and the other one is in Europe. Show different currency symbols on different jobs.
				</p>
			</li>
			<li>You can also change the information in existing job ads/openings, just click the on the job or the edit button and make the necessary changes</li>
			<li>Use the filters to sort through your job openings, this option is especially helpful if you have a lot of active, inactive, expired, and deleted job advertisements</li>
			<li>Deactivate, delete, or restore job openings as needed</li>
			<li>You can also reactivate a previously deactivated job ad</li>
			<li>Use bulk actions to perform the same action on multiple jobs in one go</li>
			<li>Use the Job Ad Logs function to view the history of each job ad</li>
		</ul>
		<p>
			Each Job Ad will fall under a specific category. See details in the next section to find out more about how to add and delete Job Categories.
		</p>
	</div>

	<h1 class="atitle">5. Adding and deleting Job Categories</h1>
	<div class="acontent">
		<p>
			Add unlimited job categories, follow the instructions below to add a new job category.
		</p>
		<ul>
			<li>Go to job categories page and click Add New Job Category or Press Ctrl + M on your keyboard</li>
			<li>Write the Job Category name and press save</li>
			<li>You are done, wasn’t that easy?</li>
			<li>You also have the option to edit and delete existing Job Categories</li>
			<li>Use bulk actions to perform the same action on multiple Job Categories in one go</li>
			<li>Categories which have any jobs attached to them cannot be deleted</li>
		</ul>
	</div>

	<h1 class="atitle">6. Job Applications</h1>
	<div class="acontent">
		<p>
			Whenever an applicant applies for a job, a new job application will appear on the Job Applications page. You will be able to see all your undeleted job applications on this page. You can perform the following actions with any job application.
		</p>
		<ul>
			<li>View the applicant's resume and other details</li>
			<li>Shortlist him/her</li>
			<li>Schedule the interview via email (change the application's status to schedule interview and the email template that you have attached to interview invitation event will appear in the email section. Send that email by clicking send).</li>
			<li>Rate the applications</li>
			<li>Leave notes or comments on a job application</li>
			<li>Delete a job application</li>
			<li>Reject the application (the candidate will receive a rejection email if an email template is attached with this event)</li>
			<li>Select an applicant</li>
			<li>Send custom emails with or without using email templates to applicants</li>
			<li>View application logs to see job application history</li>
		</ul>
	</div>

</div>