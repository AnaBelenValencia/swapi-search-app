# frontend/Dockerfile
FROM node:20-alpine

WORKDIR /usr/src/app

COPY package.json package-lock.json ./

RUN npm install

COPY . .

# Variable para apuntar al backend
ENV VITE_API_BASE_URL=http://localhost:8000/api

EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]
