<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
let booklyForm = document.querySelector('.bookly-form')
booklyForm.classList.add('container')



setTimeout(() => {
  let buttoNext = document.querySelector('.bookly-next-step')
  console.log(buttoNext)
  buttoNext.addEventListener('click' , () =>{
      setTimeout(() => {
        let booklyDate = document.querySelectorAll('.bookly-js-first-column button')
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
      }, 1500);
  })
  
}, 2000);

</script>
<!-- end Simple Custom CSS and JS -->
