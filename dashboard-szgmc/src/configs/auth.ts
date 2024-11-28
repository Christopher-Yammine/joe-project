import { config } from 'src/configs/config'

const API_URL = config.NEXT_PUBLIC_BASE_URL

export default {
  meEndpoint: '/auth/me',
  // meEndpoint: `${API_URL}/me`,
  loginEndpoint: '/jwt/login',
  // loginEndpoint: `${API_URL}/login`,
  registerEndpoint: '/jwt/register',
  storageTokenKeyName: 'accessToken',
  onTokenExpiration: 'refreshToken' // logout | refreshToken
}
