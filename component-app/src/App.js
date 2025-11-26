import logo from './logo.svg';
import './App.css';
import { useState, useEffect } from 'react';
import axios from 'axios';

function App() {
  const [data,setData] = useState("");
  useEffect(()=>{
    axios
      .get('/',{params:{}})
      .then(response=>{
        setData(response.data)
      })
  },[])
  return (
    <>
      {data}
    </>
  );
}

export default App;

