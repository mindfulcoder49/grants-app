# AI Grants Search Tool

This project is a web application designed to help Massachusetts-based organizations search for relevant AI grants. The application leverages Laravel with Vue.js and Inertia.js to provide a seamless user experience. The tool fetches and displays grant data dynamically, allowing users to easily find funding opportunities suitable for their needs.

## Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [File Structure](#file-structure)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Dynamic Grant Search**: Enter your organization's description to find relevant AI grants.
- **Detailed Grant Information**: View comprehensive details such as grant amount, eligibility, application process, and deadlines.
- **Responsive Design**: Optimized for both desktop and mobile devices.
- **Professional Layout**: Includes a header with navigation and a footer with contact options and social media links.

## Technology Stack

- **Backend**: [Laravel](https://laravel.com/) (PHP framework)
- **Frontend**: [Vue.js](https://vuejs.org/) with [Inertia.js](https://inertiajs.com/)
- **Styling**: [Tailwind CSS](https://tailwindcss.com/)
- **Server-side Rendering**: [Inertia.js](https://inertiajs.com/)
- **API Integration**: Axios for HTTP requests

## Installation

Follow these steps to set up the project locally:

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/mindfulcoder49/grants-app.git
   cd ai-grants-search-tool
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Set Up Environment Variables**:
   Copy `.env.example` to `.env` and configure your environment variables, particularly database credentials.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run Migrations**:
   Ensure your database is set up and configured correctly in your `.env` file, then run:
   ```bash
   php artisan migrate
   ```

5. **Serve the Application**:
   ```bash
   php artisan serve
   npm run dev
   ```

6. **Access the Application**:
   Open your browser and navigate to `http://localhost:8000`.

## Configuration

- **Backend API**: Ensure you have set up the correct API endpoint in the environment variables if fetching data from external sources.
- **Database**: Configure your database settings in the `.env` file.

## Usage

- **Home Page**: Enter a brief description of your organization to search for relevant AI grants.
- **Results Page**: View a list of grants with match percentages, amounts, deadlines, and detailed descriptions.
- **About Page**: Learn more about the team, technology stack, and objectives of the AI Blueprint for Massachusetts.

## File Structure

```plaintext
├── app/                     # Application logic
│   ├── Http/Controllers/    # Controllers for handling requests
│   │   └── GrantsController.php
│   └── Models/              # Eloquent models
├── resources/
│   └── js/
│       ├── Pages/           # Vue pages (Home, Results, About)
│       └── Components/      # Vue components (Logo, LastUpdateText, GrantList)
├── public/                  # Public assets (CSS, JS, images)
├── routes/
│   ├── web.php              # Web routes
│   └── api.php              # API routes
├── .env.example             # Environment configuration example
├── package.json             # Node.js dependencies
├── webpack.mix.js           # Laravel Mix configuration
└── README.md                # Project documentation
```

## Contributing

Contributions are welcome! Please follow these steps to contribute:

1. Fork the repository.
2. Create a new branch for your feature or bugfix.
3. Make your changes.
4. Submit a pull request with a detailed explanation of your changes.

### Code Style

Ensure your code follows the PSR-12 coding standard for PHP and the Vue.js style guide.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.

