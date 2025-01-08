import { ref } from 'vue'
import {
  faFacebook,
  faGithub,
  faInstagram,
  faLinkedin,
  faTwitter,
  faViber,
  faWhatsapp,
  faYoutubeSquare,
} from '@fortawesome/free-brands-svg-icons'
import { faGlobe, type IconDefinition } from '@fortawesome/free-solid-svg-icons'

export const useMapSocialsToIcons = function (socials: Array<{ name: string; url: string }>) {
  return ref<Array<{ name: string; url: string; icon: IconDefinition }>>(
    socials.map(function (social) {
      return {
        name: social.name,
        url: social.url,
        icon: getIcon(social.name),
      }
    })
  )
}

const getIcon = function (name: string) {
  switch (name) {
    case 'Facebook':
      return faFacebook
    case 'Twitter/X':
      return faTwitter
    case 'LinkedIn':
      return faLinkedin
    case 'Github':
      return faGithub
    case 'Instagram':
      return faInstagram
    case 'Youtube':
      return faYoutubeSquare
    case 'WhatsApp':
      return faWhatsapp
    case 'Viber':
      return faViber
    default:
      return faGlobe
  }
}
