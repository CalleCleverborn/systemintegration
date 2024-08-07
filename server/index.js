require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const bodyParser = require('body-parser');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');
const stripe = require('stripe')(process.env.STRIPE_SECRET_KEY);
const twilio = require('twilio');
const { Parser } = require('json2csv');
const { js2xml } = require('xml-js');

const app = express();

// Twilio configuration
const twilioClient = twilio(process.env.TWILIO_ACCOUNT_SID, process.env.TWILIO_AUTH_TOKEN);

app.use(cors());
app.use(bodyParser.json({ type: 'application/json' }));
app.use(bodyParser.raw({ type: 'application/json' }));

// MongoDB connection
mongoose.connect(process.env.MONGODB_URI, { useNewUrlParser: true, useUnifiedTopology: true })
  .then(() => console.log('MongoDB connected'))
  .catch(err => {
    console.error('Error connecting to MongoDB:', err.message);
    process.exit(1); // Exit process with failure
  });

const productSchema = new mongoose.Schema({
  name: String,
  price: Number,
  image: String,
});

const Product = mongoose.model('Product', productSchema);

const userSchema = new mongoose.Schema({
  username: { type: String, unique: true },
  password: String,
  phoneNumber: String,  // Add a phoneNumber field to the user schema
});

const User = mongoose.model('User', userSchema);

// Authentication Routes
app.post('/api/register', async (req, res) => {
  const { username, password, phoneNumber } = req.body;
  const hashedPassword = await bcrypt.hash(password, 10);
  const newUser = new User({ username, password: hashedPassword, phoneNumber });
  await newUser.save();
  res.json({ message: 'User registered' });
});

app.post('/api/login', async (req, res) => {
  const { username, password } = req.body;
  const user = await User.findOne({ username });
  if (!user) return res.status(400).json({ message: 'User not found' });
  const isMatch = await bcrypt.compare(password, user.password);
  if (!isMatch) return res.status(400).json({ message: 'Invalid credentials' });

  const token = jwt.sign({ userId: user._id }, process.env.JWT_SECRET, { expiresIn: '1h' });
  res.json({ token, userId: user._id, phoneNumber: user.phoneNumber });
});

// Product Routes
app.get('/api/products', async (req, res) => {
  try {
    const products = await Product.find().lean();
    res.json(products);
  } catch (err) {
    res.status(500).json({ message: 'Server error' });
  }
});

app.post('/api/products', async (req, res) => {
  const newProduct = new Product(req.body);
  const savedProduct = await newProduct.save();
  res.json(savedProduct);
});

app.get('/api/products/:id', async (req, res) => {
  try {
    const product = await Product.findById(req.params.id).lean();
    if (!product) {
      return res.status(404).json({ message: 'Product not found' });
    }
    res.json(product);
  } catch (err) {
    res.status(500).json({ message: 'Server error' });
  }
});

app.put('/api/products/:id', async (req, res) => {
  try {
    const updatedProduct = await Product.findByIdAndUpdate(req.params.id, req.body, { new: true }).lean();
    if (!updatedProduct) {
      return res.status(404).json({ message: 'Product not found' });
    }
    res.json(updatedProduct);
  } catch (err) {
    res.status(500).json({ message: 'Server error' });
  }
});

app.delete('/api/products/:id', async (req, res) => {
  try {
    await Product.findByIdAndDelete(req.params.id);
    res.json({ message: 'Product deleted' });
  } catch (err) {
    res.status(500).json({ message: 'Server error' });
  }
});

// Stripe Checkout Route
app.post('/api/checkout', async (req, res) => {
  const { productId, userId } = req.body;
  try {
    const product = await Product.findById(productId).lean();
    if (!product) {
      return res.status(404).json({ message: 'Product not found' });
    }

    const user = await User.findById(userId).lean();
    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }

    const session = await stripe.checkout.sessions.create({
      payment_method_types: ['card'],
      line_items: [
        {
          price_data: {
            currency: 'usd',
            product_data: {
              name: product.name,
            },
            unit_amount: product.price * 100,
          },
          quantity: 1,
        },
      ],
      mode: 'payment',
      success_url: 'http://localhost:8000/success.html',
      cancel_url: 'http://localhost:8000/cancel.html',
      metadata: {
        userId: user._id.toString()  // Store user ID in session metadata
      }
    });

    res.json({ id: session.id });
  } catch (err) {
    console.error('Error creating checkout session:', err);
    res.status(500).json({ message: 'Server error' });
  }
});

// Webhook handler for Stripe events
app.post('/webhook', bodyParser.raw({ type: 'application/json' }), (req, res) => {
  const sig = req.headers['stripe-signature'];
  let event;

  try {
    event = stripe.webhooks.constructEvent(req.body, sig, process.env.STRIPE_WEBHOOK_SECRET);
  } catch (err) {
    console.log(`Webhook signature verification failed.`, err.message);
    return res.status(400).send(`Webhook Error: ${err.message}`);
  }

  // Handle the checkout.session.completed event
  if (event.type === 'checkout.session.completed') {
    const session = event.data.object;

    // Fulfill the purchase
    handleCheckoutSession(session);
  }

  // Return a response to acknowledge receipt of the event
  res.json({ received: true });
});

const handleCheckoutSession = async (session) => {
  try {
    const userId = session.metadata.userId;  // Retrieve user ID from session metadata
    const user = await User.findById(userId);

    if (user) {
      const productName = session.display_items[0].custom.name;
      const productPrice = session.display_items[0].amount / 100;

      // Send SMS confirmation
      const message = `Thank you for your purchase of ${productName} for $${productPrice}. Your order will be processed shortly.`;
      await twilioClient.messages.create({
        body: message,
        from: process.env.TWILIO_PHONE_NUMBER,
        to: user.phoneNumber
      });
    }
  } catch (err) {
    console.error('Error handling checkout session:', err.message);
  }
};

// Export to CSV
app.get('/api/export/csv', async (req, res) => {
  try {
    const products = await Product.find().lean();
    const cleanedProducts = products.map(product => ({
      _id: product._id.toString(),
      name: product.name,
      price: product.price,
      image: product.image
    }));

    const fields = ['_id', 'name', 'price', 'image'];
    const json2csvParser = new Parser({ fields });
    const csv = json2csvParser.parse(cleanedProducts);

    res.header('Content-Type', 'text/csv');
    res.attachment('products.csv');
    res.send(csv);
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Server error' });
  }
});

// Export to XML
app.get('/api/export/xml', async (req, res) => {
  try {
    const products = await Product.find().lean();
    const cleanedProducts = products.map(product => ({
      _id: product._id.toString(),
      name: product.name,
      price: product.price,
      image: product.image
    }));

    const xml = js2xml(cleanedProducts, { compact: true, ignoreComment: true, spaces: 4 });

    res.header('Content-Type', 'application/xml');
    res.attachment('products.xml');
    res.send(xml);
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Server error' });
  }
});

const port = process.env.PORT || 3000;
app.listen(port, () => console.log(`Server running on port ${port}`));
