# 🚀 Symfony API with JWT Authentication & Custom Error Handling

## 📌 Project Overview

This project is a RESTful API built using Symfony 7.2.3. It includes user authentication via JWT (JSON Web Token), custom error handling, and CLI commands for managing users.


## ⚙️ Installation



**1️⃣ Clone the Repository**

    git clone https://github.com/Outlines1/symfony_project_Daniel_Anufriiev_2025.git  
    cd symfony_project_Daniel_Anufriiev_2025  


**2️⃣ Install Dependencies**

    composer install  


**3️⃣ Set Up Environment Variables**  
Update the `.env` file:

> `DATABASE_URL="postgresql://postgres:postgresql@localhost:5432/symfony_db"`


**4️⃣ Set Up Database**

    php bin/console doctrine:database:create  
    php bin/console doctrine:migrations:migrate  


**6️⃣ Run Symfony Server**

    symfony server:start  

The API will be available at `http://127.0.0.1:8000/`


## **🔑 Authentication (JWT)**


**🔹 Register a User**

- Endpoint: `POST /api/register`
- Request Body:
  `{  
  "email": "test@example.com",  
  "password": "securepassword"  
  }`
- Response:
  `{  
  "message": "User successfully registered",  
  "user": { "email": "test@example.com" }  
  } `



**🔹 Login & Get JWT Token**

- Endpoint: `POST /api/login`
- Request Body:
  `{  
  "email": "test@example.com",  
  "password": "securepassword"  
  } `
- Response:
  `{  
  "user": "test@example.com",  
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpX..."  
  } `

**📌 Query Builder (QB) Example**  
Custom repository using Doctrine Query Builder.

    http://127.0.0.1:8000/api/users/email/search?email=user1@example.com  


**

## ❌ Custom Error Handling*

This project includes global exception handling for API responses.

**🔹 Example: Trigger Custom Error**

- Endpoint:  `GET /api/test-error`
- Response:
  `{  
  "error": true,  
  "message": "This is a custom error message!"  
  } `


## 🛠 Custom CLI Commands

**🔹 Add a New User via CLI**

    php bin/console app:add-user test@example.com password123 ROLE_ADMIN  


**🔹 List All Users via CLI**

    php bin/console app:list-users 