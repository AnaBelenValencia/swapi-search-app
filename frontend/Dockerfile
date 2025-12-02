# frontend/Dockerfile
FROM node:20-alpine

WORKDIR /usr/src/app

# Create node_modules directory with correct permissions
RUN mkdir -p /usr/src/app/node_modules && chown -R node:node /usr/src/app

COPY package.json package-lock.json ./

RUN npm install

COPY . .

# Ensure proper permissions
RUN chown -R node:node /usr/src/app

# Variable para apuntar al backend
ENV VITE_API_BASE_URL=http://localhost:8000/api

EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]
