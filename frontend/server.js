const express = require("express");
const mongoose = require("mongoose");
const cors = require("cors");

const app = express();

app.use(cors());
app.use(express.json());

mongoose.connect("mongodb://localhost:27017/bookingDB");

app.use("/api/bookings", require("./routes/bookings"));

app.listen(3000, () => {
  console.log("Server running on port 3000");
});