export const config = {
  NEXT_PUBLIC_BASE_URL: process.env.NEXT_PUBLIC_BASE_URL || 'https://api.obsrvr.ai',
  NEXT_PUBLIC_JWT_EXPIRATION: process.env.NEXT_PUBLIC_JWT_EXPIRATION,
  NEXT_PUBLIC_JWT_SECRET: process.env.NEXT_PUBLIC_JWT_SECRET,
  NEXT_PUBLIC_JWT_REFRESH_TOKEN_SECRET: process.env.NEXT_PUBLIC_JWT_REFRESH_TOKEN_SECRET
}