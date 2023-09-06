<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Management</title>
  <style>
    body,
    h1,
    h2,
    p {
      margin: 0;
      padding: 0;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: #f2f2f2;
      padding: 20px;
    }

    h1 {
      font-size: 24px;
      margin-bottom: 20px;
    }

    h2 {
      font-size: 20px;
      margin-top: 20px;
      margin-bottom: 10px;
    }

    form {
      background-color: #fff;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    input[type="file"] {
      border: 1px solid #ccc;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 10px;
    }

    button[type="submit"] {
      background-color: #007bff;
      color: #fff;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    p {
      margin-bottom: 10px;
    }

    h3 {
      font-size: 18px;
      margin-top: 20px;
      margin-bottom: 10px;
    }

    p {
      font-size: 16px;
    }
  </style>
</head>

<body>
  <h1>Data Management</h1>
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
    <input type="file" name="csv_file" accept=".csv" />
    <button type="submit">Import CSV</button>
  </form>
  <h2>Reports</h2>
  <?php
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_FILES["csv_file"]) && $_FILES["csv_file"]["error"] == 0) {
      $file_name = $_FILES["csv_file"]["tmp_name"];
      $file = fopen($file_name, "r");
      if ($file !== false) {
        // Database connection setup (use your credentials)
        $conn = new mysqli("localhost", "root", "", "honeybee");

        if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
        }

        $total_name_count = 0;
        $city_data = [];
        $category_city_data = [];

        while (($data = fgetcsv($file)) !== false) {

          $name = mysqli_real_escape_string($conn, $data[0]);
          $email = mysqli_real_escape_string($conn, $data[1]);
          $city = mysqli_real_escape_string($conn, $data[2]);
          $mobile = mysqli_real_escape_string($conn, $data[3]);
          $category = mysqli_real_escape_string($conn, $data[4]);

          if ($name != "Name" && $email != "Email" && $city != "City" && $mobile != "Mobile No." && $category != "Category") {



            $sql = "SELECT id FROM listings WHERE name='$name' AND city='$city'";
            $result = $conn->query($sql);

            if ($result->num_rows == 0) {
              $insert_sql = "INSERT INTO listings (name, email, city, mobile, category) VALUES ('$name', '$email', '$city', '$mobile', '$category')";
              if ($conn->query($insert_sql) === true) {
                $total_name_count++;
                if (!isset($city_data[$city])) {
                  $city_data[$city] = 1;
                } else {
                  $city_data[$city]++;
                }

                $category_city = $category . " - " . $city;
                if (!isset($category_city_data[$category_city])) {
                  $category_city_data[$category_city] = 1;
                } else {
                  $category_city_data[$category_city]++;
                }
              } else {
                echo "Error: " . $insert_sql . "<br>" . $conn->error;
              }
            }
          }
        }


        $sql = "DELETE FROM listings WHERE 1";
        $result = $conn->query($sql);

        fclose($file);
        $conn->close();


        echo "<p>Total Name Count: $total_name_count</p>";
        echo "<h3>City Wise Data</h3>";
        foreach ($city_data as $city => $count) {
          // echo "<p>$city</p>";
          echo "<p>$city: $count</p>";
        }

        echo "<h3>Category + City Wise Data</h3>";
        foreach ($category_city_data as $category_city => $count) {
          echo "<p>$category_city: $count</p>";
          // echo "<p>$category_city</p>";
        }
      }
    } else {
      echo "Error uploading file.";
    }
  }
  ?>

</body>

</html>