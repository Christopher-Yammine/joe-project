// ** MUI Imports
import Card from '@mui/material/Card'
import Typography from '@mui/material/Typography'
import CardContent from '@mui/material/CardContent'
import Grid, { GridProps } from '@mui/material/Grid'
import { styled, useTheme } from '@mui/material/styles'
import React from 'react'
import { useTranslation } from 'react-i18next'

// Styled Grid component
const StyledGrid = styled(Grid)<GridProps>(({ theme }) => ({
  [theme.breakpoints.down('sm')]: {
    order: -1,
    display: 'flex',
    justifyContent: 'center'
  }
}))

// Styled component for the image
const Img = styled('img')(({ theme }) => ({
  right: 60,
  bottom: -1,
  height: 140,
  position: 'absolute',
  [theme.breakpoints.down('sm')]: {
    position: 'static'
  }
}))

interface Props {
  chapter?: number
  verse?: number
  verseCardTextKey: string
}

const VerseCard: React.FC<Props> = ({ chapter = 4, verse = 16, verseCardTextKey }) => {
  // ** Hook
  const theme = useTheme()

  const { t } = useTranslation()

  return (
    <Grid item xs={12}>
      <Card sx={{ position: 'relative' }}>
        <CardContent sx={{ py: theme => `${theme.spacing(5)} !important` }}>
          <Grid container spacing={6}>
            <Grid item xs={12} sm={6} sx={{ textAlign: ['center', 'start'] }}>
              <Typography variant='h5' sx={{ mb: 4, color: '#ae9e85' }}>
                {t('chapter')} {chapter}, {t('verse')} {verse}
              </Typography>
              <Typography maxWidth={'1000px'}>{t(verseCardTextKey)}</Typography>
            </Grid>
            <StyledGrid item xs={12} sm={6}>
              <Img alt='Congratulations John' src={`/images/cards/illustration-john-${theme.palette.mode}.png`} />
            </StyledGrid>
          </Grid>
        </CardContent>
      </Card>
    </Grid>
  )
}

export default VerseCard
