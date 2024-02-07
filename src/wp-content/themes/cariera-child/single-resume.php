<?php

get_header();
do_action( 'cariera_single_listing_data' );

while ( have_posts() ) :
	the_post();

	do_action( 'cariera_single_resume_start' );

	get_job_manager_template( 'content-single-resume.php', [], 'wp-job-manager-resumes' );

	do_action( 'cariera_single_resume_end' );

endwhile;

	

	 if (is_user_logged_in() && current_user_can('employer')) {
			echo do_shortcode('[bookly-form hide="categories,date,week_days,time_range"]');
	 }

get_footer();
?>



<script>
let booklyForm = document.querySelector('.bookly-form')
booklyForm.classList.add('container')

setTimeout(() => {
  let buttoNext = document.querySelector('.bookly-next-step')
  console.log(buttoNext)
  buttoNext.addEventListener('click' , () =>{
      setTimeout(() => {
        let booklyDate = document.querySelectorAll('.bookly-hour')
        booklyDate.forEach((btn) => {
        console.log(btn)
        btn.addEventListener('click', () => {
          setTimeout(() => {
            let booklyNome = document.querySelector('.bookly-js-full-name')
            let booklyTelefone = document.querySelector('.bookly-js-user-phone-input')
            let booklyEmail = document.querySelector('.bookly-js-user-email')

            let candidateNome = document.querySelector('.candidate h1').innerText
            let candidateTelefone = document.querySelector('#jmfe-custom-candidate_celular').innerText
            let candidateEmail = document.querySelector('.candidate-email a').href
            let candidateEmailReplace = candidateEmail.replace('mailto:', '')

            console.log(booklyNome, booklyTelefone, booklyEmail)

            booklyNome.value = candidateNome
            booklyTelefone.value = candidateTelefone
            booklyEmail.value = candidateEmailReplace
            

            console.log(booklyNome)
          }, 2000);
        })
      })
      }, 2000);
  })
  
}, 1000);

	





</script>