/**
 * @description Initializes a reCAPTCHA element on the page.
 *
 * This function removes any existing reCAPTCHA element with the provided ID and then creates a new script element
 * that loads the reCAPTCHA API from Google. The script element is added to the document head.
 */
export function useRecaptcha(elementId: string): void {
  const el = document.getElementById(elementId)
  if (document.contains(el)) el?.remove()

  const recaptchaScript = document.createElement('script')
  recaptchaScript.setAttribute('id', elementId)
  recaptchaScript.setAttribute('src', 'https://www.google.com/recaptcha/api.js')
  document.head.appendChild(recaptchaScript)
}
