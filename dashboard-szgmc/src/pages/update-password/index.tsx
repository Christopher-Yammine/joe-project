// ** React Imports
import { ReactNode, useState } from 'react'

// ** MUI Components
import Button from '@mui/material/Button'
import TextField from '@mui/material/TextField'
import Box, { BoxProps } from '@mui/material/Box'
import Typography from '@mui/material/Typography'
import useMediaQuery from '@mui/material/useMediaQuery'
import { styled, useTheme } from '@mui/material/styles'

// ** Configs
import themeConfig from 'src/configs/themeConfig'

// ** Layout Import
import BlankLayout from 'src/@core/layouts/BlankLayout'

// ** Hooks
import { useSettings } from 'src/@core/hooks/useSettings'
import { useTranslation } from 'react-i18next'
import { FormControl } from '@mui/material'
import { Controller, SubmitHandler, useForm } from 'react-hook-form'
import { useRouter } from 'next/router'

// Styled Components
const UpdatePasswordIllustration = styled('img')({
  height: 'auto',
  maxWidth: '100%'
})

const RightWrapper = styled(Box)<BoxProps>(({ theme }) => ({
  width: '100%',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  padding: theme.spacing(6),
  backgroundColor: theme.palette.background.paper,
  [theme.breakpoints.up('lg')]: {
    maxWidth: 480
  },
  [theme.breakpoints.up('xl')]: {
    maxWidth: 635
  },
  [theme.breakpoints.up('sm')]: {
    padding: theme.spacing(12)
  }
}))

type FormData = {
  password: string
}

const UpdatePassword = () => {
  // ** Hooks
  const theme = useTheme()
  const { settings } = useSettings()
  const hidden = useMediaQuery(theme.breakpoints.down('lg'))
  const { t } = useTranslation()

  // ** Var
  const { skin } = settings

  const baseURL = process.env.NEXT_PUBLIC_BASE_URL
  const {
    control,
    handleSubmit,
    formState: { errors }
  } = useForm<FormData>()

  const [isSubmitting, setIsSubmitting] = useState(false)
  const [isSuccess, setIsSuccess] = useState(false)

  const router = useRouter()
  const { token, email } = router.query

  const handleSendResetLink: SubmitHandler<FormData> = async data => {
    try {
      setIsSubmitting(true)
      const response = await fetch(`${baseURL}/update-password`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          email,
          token,
          password: data.password
        })
      })

      if (response.ok) {
        setIsSuccess(true)
      } else {
        setIsSuccess(false)
      }

      const result = await response.json()
      router.replace('/login')
    } catch (error) {
      setIsSuccess(false)
      console.error(error)
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <Box className='content-right'>
      {!hidden ? (
        <Box sx={{ p: 12, flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          <UpdatePasswordIllustration
            width={700}
            alt='forgot-password-illustration'
            src={`/images/pages/mosque-home-${theme.palette.mode}.jpg`}
          />
        </Box>
      ) : null}
      <RightWrapper
        sx={{ ...(skin === 'bordered' && !hidden && { borderLeft: `1px solid ${theme.palette.divider}` }) }}
      >
        <Box sx={{ mx: 'auto', maxWidth: 400 }}>
          <Box sx={{ mb: 8, display: 'flex', alignItems: 'center' }}>
            <Typography
              variant='h5'
              sx={{
                ml: 2,
                lineHeight: 1,
                fontWeight: 700,
                letterSpacing: '-0.45px',
                textTransform: 'lowercase',
                fontSize: '1.75rem !important'
              }}
            >
              {themeConfig.templateName}
            </Typography>
          </Box>
          <Typography variant='h6' sx={{ mb: 1.5 }}>
            {t('enterPassword')} 🔒
          </Typography>
          <form onSubmit={handleSubmit(handleSendResetLink)} noValidate autoComplete='off'>
            <FormControl fullWidth sx={{ mb: 4 }}>
              <Controller
                name='password'
                control={control}
                rules={{
                  required: 'Password is required',
                  minLength: {
                    value: 6,
                    message: 'Password must be at least 6 characters'
                  }
                }}
                defaultValue=''
                render={({ field }) => (
                  <TextField
                    {...field}
                    value={field.value || ''}
                    autoFocus
                    type='password'
                    label={t('Password')}
                    error={Boolean(errors.password)}
                    helperText={errors.password?.message}
                    sx={{ display: 'flex', mb: 6 }}
                  />
                )}
              />
            </FormControl>

            <Button
              fullWidth
              size='large'
              type='submit'
              variant='contained'
              sx={{ mb: 4 }}
              disabled={isSubmitting || isSuccess}
            >
              {isSubmitting ? t('Saving') : isSuccess ? t('Saved') : t('savePassword')}
            </Button>
          </form>
        </Box>
      </RightWrapper>
    </Box>
  )
}

UpdatePassword.getLayout = (page: ReactNode) => <BlankLayout>{page}</BlankLayout>

UpdatePassword.guestGuard = true

export default UpdatePassword
