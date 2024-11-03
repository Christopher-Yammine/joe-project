# Use an official Node runtime as a parent image
FROM node:18-alpine

# Set the working directory
WORKDIR /usr/src/app

# Install dependencies
# A wildcard is used to ensure both package.json AND package-lock.json are copied
COPY package*.json ./

# Configure npm retry and timeout settings
RUN npm config set fetch-retry-maxtimeout 6000000 -g \
    && npm config set fetch-retry-mintimeout 6000000 -g \
    && npm config set fetch-retry-factor 10 -g \
    && npm config set fetch-timeout 1200000

# Install npm packages with retry logic
RUN for i in {1..5}; do npm install --verbose --force && break || sleep 15; done

# Bundle app source inside Docker image
COPY . .

# Build the Next.js application
RUN npm run build

# Your app binds to port 3000 so you'll use the EXPOSE instruction to have it mapped by the docker daemon
EXPOSE 3000

# Defines the command to run your app. CMD sets default command and/or parameters, which can be overwritten from command line when docker container runs.
CMD ["npm", "start"]
