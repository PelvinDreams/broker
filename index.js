import  express from 'express';
import bodyParser from 'body-parser';
import axios, {isCancel, AxiosError} from 'axios';

const app = express();
const port = 3000;



async function getPackages() {
    try {
      const response = await axios.get('https://app.astrofx.pivotserver.com/api/index.php/getpackages');
  
      if (response.status === 200) {
        return response.data; // Assuming the API returns an array of packages
      } else {
        console.error('Error fetching packages:', response.statusText);
        return []; // Handle errors gracefully, e.g., return an empty array
      }
    } catch (error) {
      console.error('Error fetching packages:', error);
      return []; // Handle errors gracefully, e.g., return an empty array
    }
  }
  
  // Example usage:
  getPackages()
    .then(packages => {
      console.log('Packages:', packages);
      // Process the retrieved package data here
    })
    .catch(error => {
      console.error('Error retrieving packages:', error);
    });
  






app.listen(port, () => {
console.log('listening on port: ${port}');
})